<?php

namespace Azuriom\Plugin\Draw\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Server;
use Azuriom\Plugin\Draw\Models\DrawReward;
use Azuriom\Plugin\Draw\Models\DrawRewardServer;
use Illuminate\Http\Request;

class RewardsController extends Controller
{
    public function index()
    {
        return view('draw::admin.rewards', [
            'rewards' => DrawReward::all()
        ]);
    }

    public function add()
    {
        return view('draw::admin.rewards_edit', [
            'servers' => Server::executable()->get(),
        ]);
    }

    public function add_submit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'money' => 'nullable|numeric|min:0',
            'commands' => 'nullable|array',
            'commands.*' => 'string',
            'servers' => 'array',
            'servers.*' => 'string',
        ]);

        $commands = $request->input('commands', []);
        $servers = $request->input('servers', []);

        if(!empty($commands) && empty($servers)) {
            return redirect()
                ->back()
                ->with('error', trans('draw::admin.you_must_select_a_server'));
        }

        $reward = DrawReward::create([
            'name' => $request->input('name'),
            'money' => $request->input('money', 0),
            'need_online' => $request->boolean('need_online'),
            'commands' => $request->input('commands', []),
        ]);

        foreach($request->input('servers', []) as $serverId) {
            DrawRewardServer::create([
                'reward_id' => $reward->id,
                'server_id' => $serverId,
            ]);
        }

        return redirect()
            ->route('draw.admin.rewards')
            ->with('success', trans('draw::admin.reward_correctly_added'));
    }

    public function edit($id)
    {
        $reward = DrawReward::with('servers')->findOrFail($id);

        return view('draw::admin.rewards_edit', [
            'reward' => $reward,
            'servers' => Server::executable()->get(),
        ]);
    }

    public function edit_submit(Request $request, $id)
    {
        $reward = DrawReward::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'money' => 'nullable|numeric|min:0',
            'commands' => 'nullable|array',
            'commands.*' => 'string',
            'servers' => 'array',
            'servers.*' => 'string',
        ]);
 
        $commands = $request->input('commands', []);
        $servers = $request->input('servers', []);

        if(!empty($commands) && empty($servers)) {
            return redirect()
                ->back()
                ->with('error', trans('draw::admin.you_must_select_a_server'));
        }

        $reward->update([
            'name' => $request->input('name'),
            'money' => $request->input('money', 0),
            'need_online' => $request->boolean('need_online'),
            'commands' => $request->input('commands', []),
        ]);

        $newServerIds = $request->input('servers', []);
        $existingServerIds = $reward->servers->pluck('id')->toArray();

        DrawRewardServer::where('reward_id', $reward->id)
            ->whereNotIn('server_id', $newServerIds)
            ->delete();

        foreach(array_diff($newServerIds, $existingServerIds) as $serverId) {
            DrawRewardServer::create([
                'reward_id' => $reward->id,
                'server_id' => $serverId,
            ]);
        }

        return redirect()
            ->route('draw.admin.rewards')
            ->with('success', trans('draw::admin.reward_correctly_saved'));
    }

    public function delete($id)
    {
        DrawReward::find($id)->delete();

        return redirect()
            ->back()
            ->with('success', trans('draw::admin.reward_correctly_deleted'));
    }
}
