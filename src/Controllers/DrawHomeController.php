<?php

namespace Azuriom\Plugin\Draw\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Draw\Models\Draw;
use Illuminate\Support\Facades\Auth;
use Azuriom\Plugin\Draw\Models\DrawEntries;
use Azuriom\Plugin\Draw\Models\DrawWinners;

class DrawHomeController extends Controller
{
    public function index()
    {
        $activeDraws = Draw::query()
            ->orderByDesc('pined')
            ->orderByDesc('is_open')
            ->orderByDesc('created_at')
            ->get();

        $entries = collect();
        $winners = collect();

        if(Auth::user()) {
            $userId = Auth::user()->id;
            $entries = DrawEntries::where('user_id', $userId)->get();
            $winners = DrawWinners::get()->groupBy('draw_id');
        }

        return view('draw::index', [
            'activeDraws' => $activeDraws,
            'entries' => $entries,
            'winners' => $winners
        ]);
    }

    public function participate($draw_id) 
    {
        $draw = Draw::find( $draw_id);

        if(!$draw) {
            return redirect()
                ->back()
                ->with('error', trans('draw::messages.errors.draw_not_found'));
        }

        $user = Auth::user();

        if(!$user) {
            return redirect()
                ->back()
                ->with('error', trans('draw::messages.errors.must_be_authentificated'));
        }

        if(!$draw->is_open) {
            return redirect()
                ->back()
                ->with('error', trans('draw::messages.errors.draw_stopped'));
        }

        if($draw->closed) {
            return redirect()
                ->back()
                ->with('error', trans('draw::messages.errors.draw_closed'));
        }

        if($draw->expires_at < now()) {
            Draw::find($draw_id)->update([
                'closed' => true,
                'is_open' => false,
            ]);
            return redirect()
                ->back()
                ->with('error', trans('draw::messages.errors.draw_closed'));
        }

        
        if($draw->price > 0) {
            if($user->money < $draw->price) {
                return redirect()
                    ->back()
                    ->with('error', trans('draw::messages.errors.not_enough_money'));
            }
            $user->money -= $draw->price;
            $user->save();
        }
        
        $max_entries_per_player = $draw->max_entries_per_player;
        $max_entries = $draw->max_entries;

        $pentries = DrawEntries::where('user_id', $user->id)->where('draw_id', $draw->id)->count();
        $tentries = DrawEntries::where('draw_id', $draw->id)->count();

        if(($max_entries_per_player > 0 && $pentries >= $max_entries_per_player)
            || ($max_entries > 0 && $tentries >= $max_entries)) {
            return redirect()
                ->back()
                ->with('error', trans('draw::messages.errors.max_entries_reached'));
        }

        $this->insertDraw($draw, $user);
        return redirect()
            ->back()
            ->with('success', trans('draw::messages.entry_added'));
    }

    private function insertDraw($draw, $user) {
        DrawEntries::create([
            'draw_id' => $draw->id,
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
