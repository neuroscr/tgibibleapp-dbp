<?php

namespace App\Http\Controllers\Collection;

use App\Traits\AccessControlAPI;
use App\Http\Controllers\APIController;
use App\Models\Collection\Collection;
use App\Models\Collection\CollectionPlaylist;
use App\Models\Language\Language;
use App\Traits\CheckProjectMembership;
use Illuminate\Http\Request;

class CollectionsController extends APIController
{
    use AccessControlAPI;
    use CheckProjectMembership;
    //

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/collections",
     *     tags={"Collections"},
     *     summary="List a user's collections",
     *     operationId="v4_collections.index",
     *     @OA\Parameter(
     *          name="featured",
     *          in="query",
     *          @OA\Schema(ref="#/components/schemas/Plan/properties/featured"),
     *          description="Return featured collections"
     *     ),
     *     security={{"api_token":{}}},
     *     @OA\Parameter(
     *          name="iso",
     *          in="query",
     *          @OA\Schema(ref="#/components/schemas/Language/properties/iso"),
     *          description="The iso code to filter collections by. For a complete list see the `iso` field in the `/languages` route"
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/limit"),
     *     @OA\Parameter(ref="#/components/parameters/page"),
     *     @OA\Parameter(ref="#/components/parameters/sort_by"),
     *     @OA\Parameter(ref="#/components/parameters/sort_dir"),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/v4_collection_index")),
     *         @OA\MediaType(mediaType="application/xml",  @OA\Schema(ref="#/components/schemas/v4_collection_index")),
     *         @OA\MediaType(mediaType="text/x-yaml",      @OA\Schema(ref="#/components/schemas/v4_collection_index")),
     *         @OA\MediaType(mediaType="text/csv",      @OA\Schema(ref="#/components/schemas/v4_collection_index"))
     *     )
     * )
     *
     *
     * @return mixed
     *
     *
     * @OA\Schema (
     *   type="object",
     *   schema="v4_collection_index_detail",
     *   allOf={
     *      @OA\Schema(ref="#/components/schemas/v4_collection"),
     *   },
     *   @OA\Property(property="total_days", type="integer")
     * )
     *
     * @OA\Schema (
     *   type="object",
     *   schema="v4_collection_index",
     *   description="The v4 collection index response.",
     *   title="User collections",
     *   allOf={
     *      @OA\Schema(ref="#/components/schemas/pagination"),
     *   },
     *   @OA\Property(
     *      property="data",
     *      type="array",
     *      @OA\Items(ref="#/components/schemas/v4_collection_index_detail")
     *   )
     * )
     */


    public function index(Request $request)
    {
        $user = $request->user();

        // Validate Project / User Connection
        if (!empty($user) && !$this->compareProjects($user->id, $this->key)) {
            return $this->setStatusCode(401)->replyWithError(trans('api.projects_users_not_connected'));
        }

        $featured = checkBoolean('featured') || empty($user);
        $limit        = (int) (checkParam('limit') ?? 25);
        $sort_by    = checkParam('sort_by') ?? 'name';
        $sort_dir   = checkParam('sort_dir') ?? 'asc';
        $iso = checkParam('iso');

        $language_id = cacheRemember('v4_language_id_from_iso', [$iso], now()->addDay(), function () use ($iso) {
            return optional(Language::where('iso', $iso)->select('id')->first())->id;
        });

        if ($featured) {
            $cache_params = [$featured, $limit, $sort_by, $sort_dir, $iso];
            $collections = cacheRemember('v4_collection_index', $cache_params, now()->addDay(), function () use ($featured, $limit, $sort_by, $sort_dir, $user, $language_id) {
                return $this->getCollections($featured, $limit, $sort_by, $sort_dir, $user, $language_id);
            });
            return $this->reply($collections);
        }

        return $this->reply($this->getCollections($featured, $limit, $sort_by, $sort_dir, $user, $language_id));
    }

    private function getCollections($featured, $limit, $sort_by, $sort_dir, $user, $language_id)
    {
        $collections = Collection::with('days')
            ->with('user')
            //->where('draft', 0)
            ->when($language_id, function ($q) use ($language_id) {
                $q->where('collections.language_id', $language_id);
            })
            ->when($featured || empty($user), function ($q) {
                $q->where('collections.featured', '1');
            }) /* ->unless($featured, function ($q) use ($user) {
                $q->join('user_plans', function ($join) use ($user) {
                    $join->on('user_plans.plan_id', '=', 'plans.id')->where('user_plans.user_id', $user->id);
                });
                $q->select(['plans.*', 'user_plans.start_date', 'user_plans.percentage_completed']);
            }) */
            ->orderBy($sort_by, $sort_dir)->paginate($limit);

        /*
        foreach ($collections as $collection) {
            $collection->total_days = sizeof($collection->days);
            unset($collection->days);
        }
        */
        return $collections;
    }

    /**
     * Store a newly created collection in storage.
     *
     *  @OA\Post(
     *     path="/collections",
     *     tags={"Collections"},
     *     summary="Crete a collection",
     *     operationId="v4_collections.store",
     *     security={{"api_token":{}}},
     *     @OA\RequestBody(required=true, description="Fields for User Collection Creation",
     *           @OA\MediaType(mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="name", ref="#/components/schemas/Plan/properties/name"),
     *                  @OA\Property(property="suggested_start_date", ref="#/components/schemas/Plan/properties/suggested_start_date"),
     *                  @OA\Property(property="days",type="integer")
     *              )
     *          )
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/collection")
     * )
     *
     * @return \Illuminate\Http\Response|array
     */
    public function store(Request $request)
    {

        // Validate Project / User Connection
        $user = $request->user();
        $user_is_member = $this->compareProjects($user->id, $this->key);

        if (!$user_is_member) {
            return $this->setStatusCode(401)->replyWithError(trans('api.projects_users_not_connected'));
        }

        $name = checkParam('name', true);
        $days = intval(checkParam('days', true));
        $days = $days > $this->days_limit ? $this->days_limit : $days;
        $suggested_start_date = checkParam('suggested_start_date');

        $collection = Collection::create([
            'user_id'               => $user->id,
            'name'                  => $name,
            'featured'              => false,
            'suggested_start_date'  => $suggested_start_date ?? ''
        ]);

        // build playlist data
        for ($i = 0; $i < intval($days); $i++) {
            $data[] = [
                'collection_id' => $collection->id,
                'name' => 'collection_' . $collection->id,
                'user_id' => $user->id
            ];
        }
        Playlist::insert($data);
        $new_playlists = Playlist::select(['id'])
            ->where('name', 'collection_' . $collection->id)
            ->where('collection_id', $collection->id)
            ->where('user_id', $user->id)
            ->get()->pluck('id');
        $collection_playlist_data = $new_playlists->map(function ($item) use ($collection) {
            return [
                'collection_id'         => $collection->id,
                'playlist_id'           => $item,
            ];
        })->toArray();
        Playlist::whereIn('id', $new_playlists)->update(['name' => '', 'updated_at' => 'created_at']);
        CollectionPlaylist::insert($collection_playlist_data);

        /*
        UserPlan::create([
            'user_id'               => $user->id,
            'collection_id'         => $collection->id
        ]);
        */

        $resp_collection = $this->getCollection($collection->id, $user);
        return $this->reply($resp_collection);
    }

    /**
     *
     * @OA\Get(
     *     path="/collections/{collection_id}",
     *     tags={"Collections"},
     *     summary="A user's collection",
     *     operationId="v4_collections.show",
     *     security={{"api_token":{}}},
     *     @OA\Parameter(
     *          name="collection_id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(ref="#/components/schemas/User/properties/id"),
     *          description="The collection id"
     *     ),
     *     @OA\Parameter(
     *          name="show_details",
     *          in="query",
     *          @OA\Schema(type="boolean"),
     *          description="Give full details of the collection"
     *     ),
     *     @OA\Parameter(
     *          name="show_text",
     *          in="query",
     *          @OA\Schema(type="boolean"),
     *          description="Enable the full details of the collection and retrieve the text of the playlists items"
     *     ),
     *     @OA\Response(response=200, ref="#/components/responses/collection")
     * )
     *
     * @param $collection_id
     *
     * @return mixed
     *
     *
     */
    public function show(Request $request, $collection_id)
    {
        $user = $request->user();

        // Validate Project / User Connection
        if (!empty($user) && !$this->compareProjects($user->id, $this->key)) {
            return $this->setStatusCode(401)->replyWithError(trans('api.projects_users_not_connected'));
        }

        $collection = $this->getCollection($collection_id, $user);

        if (!$collection) {
            return $this->setStatusCode(404)->replyWithError('Collection Not Found');
        }

        $show_details = checkBoolean('show_details');
        $show_text = checkBoolean('show_text');
        if ($show_text) {
            $show_details = $show_text;
        }

        $playlist_controller = new PlaylistsController();
        if ($show_details) {
            // days?
            /*
            foreach ($collection->days as $day) {
                $day_playlist = $playlist_controller->getPlaylist($user, $day->playlist_id);
                $day_playlist->path = route('v4_playlists.hls', ['playlist_id'  => $day_playlist->id, 'v' => $this->v, 'key' => $this->key]);
                if ($show_text) {
                    foreach ($day_playlist->items as $item) {
                        $item->verse_text = $item->getVerseText();
                    }
                }
                $day->playlist = $day_playlist;
            }
            */
        }

        return $this->reply($collection);
    }

    /**
     * Update the specified collection.
     *
     *  @OA\Put(
     *     path="/collections/{collection_id}",
     *     tags={"Collections"},
     *     summary="Update a collection",
     *     operationId="v4_collections.update",
     *     security={{"api_token":{}}},
     *     @OA\Parameter(name="collection_id", in="path", required=true, @OA\Schema(ref="#/components/schemas/Plan/properties/id")),
     *     @OA\Parameter(name="days", in="query",@OA\Schema(type="string"), description="Comma-separated ids of the days to be sorted or deleted"),
     *     @OA\Parameter(name="delete_days", in="query",@OA\Schema(type="boolean"), description="Will delete all days"),
     *     @OA\RequestBody(required=true, @OA\MediaType(mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="name", ref="#/components/schemas/Plan/properties/name"),
     *              @OA\Property(property="suggested_start_date", ref="#/components/schemas/Plan/properties/suggested_start_date")
     *          )
     *     )),
     *     @OA\Response(response=200, ref="#/components/responses/collection")
     * )
     *
     * @param  int $collection_id
     * @param  string $days
     *
     * @return array|\Illuminate\Http\Response
     */
    public function update(Request $request, $collection_id)
    {
        // Validate Project / User Connection
        $user = $request->user();
        $user_is_member = $this->compareProjects($user->id, $this->key);

        if (!$user_is_member) {
            return $this->setStatusCode(401)->replyWithError(trans('api.projects_users_not_connected'));
        }

        $collection = Collection::where('user_id', $user->id)->where('id', $collection_id)->first();

        if (!$collection) {
            return $this->setStatusCode(404)->replyWithError('Collection Not Found');
        }

        $update_values = [];

        $name = checkParam('name');
        if ($name) {
            $update_values['name'] = $name;
        }

        $suggested_start_date = checkParam('suggested_start_date');
        if ($suggested_start_date) {
            $update_values['suggested_start_date'] = $suggested_start_date;
        }

        $collection->update($update_values);

        /*
        $days = checkParam('days');
        $delete_days = checkBoolean('delete_days');

        if ($days || $delete_days) {
            $days_ids = [];
            if (!$delete_days) {
                $days_ids = explode(',', $days);
                PlanDay::setNewOrder($days_ids);
            }
            $deleted_days = PlanDay::whereNotIn('id', $days_ids)
               ->where('plan_id', $collection->id);
            $playlists_ids = $deleted_days->pluck('playlist_id')->unique();
            $playlists = Playlist::whereIn('id', $playlists_ids);
            $deleted_days->delete();
            $playlists->delete();
        }
        */

        $collection = $this->getCollection($collection->id, $user);

        return $this->reply($collection);
    }

    /**
     * Remove the specified collection.
     *
     *  @OA\Delete(
     *     path="/collections/{collection_id}",
     *     tags={"Collections"},
     *     summary="Delete a collection",
     *     operationId="v4_collections.destroy",
     *     security={{"api_token":{}}},
     *     @OA\Parameter(name="collection_id", in="path", required=true, @OA\Schema(ref="#/components/schemas/Plan/properties/id")),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\MediaType(mediaType="application/json", @OA\Schema(type="string")),
     *         @OA\MediaType(mediaType="application/xml",  @OA\Schema(type="string")),
     *         @OA\MediaType(mediaType="text/x-yaml",      @OA\Schema(type="string")),
     *         @OA\MediaType(mediaType="text/csv",      @OA\Schema(type="string"))
     *     )
     * )
     *
     * @param  int $collection_id
     *
     * @return array|\Illuminate\Http\Response
     */
    public function destroy(Request $request, $collection_id)
    {
        // Validate Project / User Connection
        $user = $request->user();
        $user_is_member = $this->compareProjects($user->id, $this->key);

        if (!$user_is_member) {
            return $this->setStatusCode(401)->replyWithError(trans('api.projects_users_not_connected'));
        }

        $collection = Collection::where('user_id', $user->id)->where('id', $collection_id)->first();

        if (!$collection) {
            return $this->setStatusCode(404)->replyWithError('Collection Not Found');
        }

        // FIXME: days, need to delete playlists
        /*
        $playlists_ids = $collection->days()->pluck('playlist_id')->unique();
        $playlists = Playlist::whereIn('id', $playlists_ids);
        $playlists->delete();
        */
        //$user_plans = UserPlan::where('collection_id', $collection_id);
        //$user_plans->delete();
        //$plan->days()->delete();
        $plan->delete();

        return $this->reply('Collection Deleted');
    }

    private function validateCollection()
    {
        $validator = Validator::make(request()->all(), [
            'name'              => 'required|string'
        ]);
        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }
        return true;
    }

    /**
     *  @OA\Schema (
     *   type="object",
     *   schema="v4_collection",
     *   @OA\Property(property="id", ref="#/components/schemas/Plan/properties/id"),
     *   @OA\Property(property="name", ref="#/components/schemas/Plan/properties/name"),
     *   @OA\Property(property="featured", ref="#/components/schemas/Plan/properties/featured"),
     *   @OA\Property(property="thumbnail", ref="#/components/schemas/Plan/properties/thumbnail"),
     *   @OA\Property(property="suggested_start_date", ref="#/components/schemas/Plan/properties/suggested_start_date"),
     *   @OA\Property(property="created_at", ref="#/components/schemas/Plan/properties/created_at"),
     *   @OA\Property(property="updated_at", ref="#/components/schemas/Plan/properties/updated_at"),
     *   @OA\Property(property="start_date", ref="#/components/schemas/UserPlan/properties/start_date"),
     *   @OA\Property(property="percentage_completed", ref="#/components/schemas/UserPlan/properties/percentage_completed"),
     *   @OA\Property(property="user", ref="#/components/schemas/v4_collection_index_user"),
     * )
     *
     * @OA\Schema (
     *   type="object",
     *   schema="v4_collection_index_user",
     *   description="The user who created the collection",
     *   @OA\Property(property="id", type="integer"),
     *   @OA\Property(property="name", type="string")
     * )
     *
     * @OA\Schema (
     *   type="object",
     *   schema="v4_collection_detail",
     *   allOf={
     *      @OA\Schema(ref="#/components/schemas/v4_collection"),
     *   },
     *   @OA\Property(property="days",type="array",@OA\Items(ref="#/components/schemas/PlanDay"))
     * )
     *
     *
     * @OA\Response(
     *   response="collection",
     *   description="collection Object",
     *   @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/v4_collection_detail")),
     *   @OA\MediaType(mediaType="application/xml",  @OA\Schema(ref="#/components/schemas/v4_collection_detail")),
     *   @OA\MediaType(mediaType="text/x-yaml",      @OA\Schema(ref="#/components/schemas/v4_collection_detail")),
     *   @OA\MediaType(mediaType="text/csv",         @OA\Schema(ref="#/components/schemas/v4_collection_detail"))
     * )
     */

    private function getCollection($collection_id, $user, $with_order = false)
    {
        $select = ['collections.*'];
        if (!empty($user)) {
            $select[] = 'user_plans.start_date';
            $select[] = 'user_plans.percentage_completed';
        }
        $plan = Collection::with('days')
            ->with('user')
            ->where('collections.id', $plan_id)
            ->when(!empty($user), function ($q) use ($user) {
                $q->leftJoin('user_plans', function ($join) use ($user) {
                    $join->on('user_plans.plan_id', '=', 'plans.id')->where('user_plans.user_id', $user->id);
                });
            })->select($select)->first();

        return $plan;
    }

}
