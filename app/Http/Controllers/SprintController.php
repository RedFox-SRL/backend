<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSprintRequest;
use App\Http\Requests\UpdateSprintRequest;
use App\Models\Sprint;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    public function index(Request $request)
    {
        $groupId = $request->query('group_id');
        return Sprint::where('group_id', $groupId)->with('tasks')->get();
    }

    public function store(StoreSprintRequest $request)
    {
        return Sprint::create($request->all());
    }

    public function show($id)
    {
        return Sprint::with('tasks')->findOrFail($id);
    }

    public function update(UpdateSprintRequest $request, $id)
    {
        $sprint = Sprint::findOrFail($id);
        $sprint->update($request->all());

        return $sprint;
    }

    public function destroy($id)
    {
        $sprint = Sprint::findOrFail($id);
        $sprint->delete();

        return response()->noContent();
    }
}
