<?php

namespace Azuriom\Plugin\Draw\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Azuriom\Plugin\Draw\Models\Draw;
use Azuriom\Plugin\Draw\Models\DrawEntries;
use Azuriom\Plugin\Draw\Models\DrawWinners;
use Azuriom\Plugin\Draw\Models\DrawReward;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Azuriom\Models\User;
use Azuriom\Notifications\AlertNotification;

class AdminController extends Controller
{
    public function index()
    {
        return view('draw::admin.index', [
            'draws' => Draw::all(),
            'entries' => DrawEntries::all(),
        ]);
    }

    public function add()
    {
        return view('draw::admin.edit', [
            'rewards' => DrawReward::get(),
        ]);
    }

    public function add_submit(Request $request) 
    {
        $request->validate([
            'rewards' => 'nullable|array',
            'rewards.*' => 'integer|exists:draw_rewards,id',
        ]);

        $title = $request->input('title');
        $desc = $request->input('description');
        $submitter = Auth::user()->id;
        $max_entries = $request->integer('max_entries_total');
        $max_entries_per_player = $request->integer('max_entries_per_player');
        $price = $request->integer('price');
        $winners = $request->integer('winners');
        $pined = $request->integer('pined') == 0;
        $is_open = $request->boolean('is_open');
        $auto_draw = $request->integer('auto_send') === 1;

        $expires_at_input = $request->input('expires_at');
        $expires_at = $expires_at_input ? Carbon::parse($expires_at_input)->format('Y-m-d H:i:s') : null;

        $now = now();

        $draw = Draw::create([
            'name' => $title,
            'description' => $desc,
            'submitter' => $submitter,
            'max_entries' => $max_entries,
            'max_entries_per_player' => $max_entries_per_player,
            'price' => $price,
            'winners' => $winners,
            'is_open' => $is_open,
            'pined' => $pined,
            'automatic_draw' => $auto_draw,
            'expires_at' => $expires_at,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $draw->rewards()->sync($request->input('rewards', []));

        return redirect()
            ->route('draw.admin.index')
            ->with('success', trans('draw::admin.draw_correctly_saved'));
    }

    public function edit($id) 
    {
        $draw = Draw::find($id);

        if(!$draw) {
            return redirect()
                ->back()
                ->with('error', trans('draw::admin.errors.draw_not_found'));
        }

        if($draw->closed) {
            return redirect()
                ->back()
                ->with('error', trans('draw::admin.errors.draw_close'));
        }

        return view('draw::admin.edit', [
            'draw' => $draw,
            'rewards' => DrawReward::get(),
        ]);
    }

    public function edit_submit(Request $request, $id) 
    {
        $draw = Draw::find($id);

        if(!$draw) {
            return redirect()
                ->back()
                ->with('error', trans('draws::admin.errors.draw_not_found'));
        }

        $request->validate([
            'rewards' => 'nullable|array',
            'rewards.*' => 'integer|exists:draw_rewards,id',
        ]);

        $name = $request->input('title');
        $desc = $request->input('description');
        $submitter = Auth::user()->id;
        $max_entries = $request->integer('max_entries_total');
        $max_entries_per_player = $request->integer('max_entries_per_player');
        $price = $request->integer('price');
        $winners = $request->integer('winners');
        $pined = $request->integer('pined') == 0;
        $is_open = $request->boolean('is_open');
        $auto_draw = $request->integer('auto_send') == 0;

        $expires_at_input = $request->input('expires_at');
        $expires_at = $expires_at_input ? Carbon::parse($expires_at_input)->format('Y-m-d H:i:s') : null;

        Draw::find($id)->update([
            'name' => $name,
            'description' => $desc,
            'submitter' => $submitter,
            'max_entries' => $max_entries,
            'max_entries_per_player' => $max_entries_per_player,
            'price' => $price,
            'winners' => $winners,
            'pined' => $pined,
            'is_open' => $is_open,
            'automatic_draw' => $auto_draw,
            'expires_at' => $expires_at,
            'updated_at' => now(),
        ]);

        $draw->rewards()->sync($request->input('rewards', []));

        return redirect()
            ->route('draw.admin.index')
            ->with('success', trans('draw::admin.draw_correctly_saved'));
    }

    public function entries($id) 
    {
        $draw = Draw::find($id);

        if(!$draw) {
            return redirect()
                ->back()
                ->with('error', trans('draw::admin.errors.draw_not_found'));
        }

        $entries = DrawEntries::where('draw_id', $id)->get();

        return view('draw::admin.entries', [
            'draw' => $draw,
            'entries' => $entries,
        ]);
    }

    public function close($id) 
    {
        $draw = Draw::find($id);

        if(!$draw) {
            return redirect()
                ->back()
                ->with('error', trans('draw::admin.errors.draw_not_found'));
        }

        $entries = DrawEntries::where('draw_id', $id)->get();

        $winners = $draw->winners;
        $total_entries = sizeof($entries);

        if($total_entries < $winners) {
            return redirect()
                ->back()
                ->with('error', trans('draw::admin.errors.not_enought_entries'));
        }

        $draw->close();

        return redirect()
            ->back()
            ->with('success', trans('draw::admin.draw_correctly_closed'));
    }

    public function stop($id)
    {
        $draw = Draw::find($id);

        if(!$draw) {
            return redirect()
                ->back()
                ->with('error', trans('draw::admin.errors.draw_not_found'));
        }
    
        $draw->update([
            'is_open' => !$draw->is_open,
        ]);

        return redirect()
            ->back()
            ->with('success',
                $draw->is_open
                    ? trans('draw::admin.draw_correctly_restart')
                    : trans('draw::admin.draw_correctly_stopped')
            );
    }

    public function replay($id) 
    {
        $draw = Draw::find($id);

        if(!$draw) {
            return redirect()
                ->back()
                ->with('error', trans('draw::admin.errors.draw_not_found'));
        }

        $entries = DrawEntries::where('draw_id', $id)->get();

        $winners = $draw->winners;
        $total_entries = sizeof($entries);

        if($total_entries < $winners) {
            return redirect()
                ->back()
                ->with('error', trans('draw::admin.errors.not_enought_entries'));
        }

        DrawWinners::where('draw_id', $id)->delete();
        $draw->close();

        return redirect()
            ->back()
            ->with('success', trans('draw::admin.draw_correctly_replayed'));
    }

    public function delete($id)
    {
        $draw = Draw::find($id);

        if(!$draw) {
            return redirect()
                ->back()
                ->with('error', trans('draw::admin.errors.draw_not_found'));
        }

        Draw::find($draw->id)->delete();

        return redirect()
            ->back()
            ->with('success', trans('draw::admin.draw_correctly_deleted'));
    }
}
