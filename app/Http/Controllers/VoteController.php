<?php

namespace App\Http\Controllers;

use App\Http\Requests\VoteRequest;
use App\Http\Resources\StudyResourceResource;
use App\Models\StudyResource;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    /**
     * Cast or change my vote. Voting again updates the same row instead of
     * adding a second one; the unique (user_id, study_resource_id) index
     * guarantees there can never be two.
     */
    public function update(VoteRequest $request, StudyResource $resource): StudyResourceResource
    {
        $resource->votes()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['value' => (int) $request->validated('value')],
        );

        return new StudyResourceResource(StudyResource::withDetails($request->user())->findOrFail($resource->id));
    }

    /**
     * Remove my vote. Removing a vote that doesn't exist is not an error.
     */
    public function destroy(Request $request, StudyResource $resource): StudyResourceResource
    {
        $resource->votes()->where('user_id', $request->user()->id)->delete();

        return new StudyResourceResource(StudyResource::withDetails($request->user())->findOrFail($resource->id));
    }
}
