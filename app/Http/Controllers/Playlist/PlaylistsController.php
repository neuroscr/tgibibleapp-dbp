<?php

namespace App\Http\Controllers\Playlist;

use App\Traits\AccessControlAPI;
use App\Http\Controllers\APIController;
use App\Models\User\User;
use App\Models\Playlist\Playlist;
use App\Traits\CheckProjectMembership;

class PlaylistsController extends APIController
{
    use AccessControlAPI;
    use CheckProjectMembership;

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *     path="/playlists/{user_id}",
     *     tags={"Playlists"},
     *     summary="List a user's playlists",
     *     description="",
     *     operationId="v4_playlists.index",
     *     @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(ref="#/components/schemas/User/properties/id"),
     *          description="The user who created the playlists"
     *     ),
     *     @OA\Parameter(
     *          name="featured",
     *          in="query",
     *          @OA\Schema(ref="#/components/schemas/Playlist/properties/featured"),
     *          description="Return featured playlists"
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/version_number"),
     *     @OA\Parameter(ref="#/components/parameters/key"),
     *     @OA\Parameter(ref="#/components/parameters/pretty"),
     *     @OA\Parameter(ref="#/components/parameters/format"),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/v4_playlist_index")),
     *         @OA\MediaType(mediaType="application/xml",  @OA\Schema(ref="#/components/schemas/v4_playlist_index")),
     *         @OA\MediaType(mediaType="text/x-yaml",      @OA\Schema(ref="#/components/schemas/v4_playlist_index")),
     *         @OA\MediaType(mediaType="text/csv",      @OA\Schema(ref="#/components/schemas/v4_playlist_index"))
     *     )
     * )
     *
     * @param $user_id
     *
     * @return mixed
     * 
     * 
     * @OA\Schema (
     *   type="array",
     *   schema="v4_playlist_index",
     *   description="The v4 playlist index response.",
     *   title="User playlist",
     *   @OA\Xml(name="v4_playlist_index"),
     *   @OA\Items(ref="#/components/schemas/Playlist")
     * )
     */
    public function index($user_id)
    {

        // Validate Project / User Connection
        $user = User::where('id', $user_id)->select('id')->first();

        if (!$user) {
            return $this->setStatusCode(404)->replyWithError(trans('api.users_errors_404'));
        }

        $user_is_member = $this->compareProjects($user_id, $this->key);

        if (!$user_is_member) {
            return $this->setStatusCode(401)->replyWithError(trans('api.projects_users_not_connected'));
        }

        $featured = checkParam('featured');
        $featured = $featured && $featured != 'false';

        $playlists = Playlist::with('items')->when($featured, function ($q) {
                $q->where('user_playlists.featured', '1');
            })->unless($featured, function ($q) use ($user_id) {
                $q->where('user_playlists.user_id', $user_id);
            })->orderBy('updated_at', 'desc')->get();

        return $this->reply($playlists);
    }

    /**
     * Store a newly created playlist in storage.
     *
     * @OA\Post(
     *     path="/playlists/{user_id}",
     *     tags={"Playlists"},
     *     summary="Crete a playlist",
     *     description="",
     *     operationId="v4_playlists.store",
     *     @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          required=true,
     *          @OA\Schema(ref="#/components/schemas/User/properties/id"),
     *          description="The user who is creating the playlist"
     *     ),
     *     @OA\Parameter(ref="#/components/parameters/version_number"),
     *     @OA\Parameter(ref="#/components/parameters/key"),
     *     @OA\Parameter(ref="#/components/parameters/pretty"),
     *     @OA\Parameter(ref="#/components/parameters/format"),
     *     @OA\RequestBody(required=true, description="Fields for User Playlist Creation", @OA\MediaType(mediaType="application/json",
     *          @OA\Schema(
     *              @OA\Property(property="name",                  ref="#/components/schemas/Playlist/properties/name"),
     *          )
     *     )),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/v4_playlist_index")),
     *         @OA\MediaType(mediaType="application/xml",  @OA\Schema(ref="#/components/schemas/v4_playlist_index")),
     *         @OA\MediaType(mediaType="text/x-yaml",      @OA\Schema(ref="#/components/schemas/v4_playlist_index")),
     *         @OA\MediaType(mediaType="text/csv",      @OA\Schema(ref="#/components/schemas/v4_playlist_index"))
     *     )
     * )
     *
     * @return \Illuminate\Http\Response|array
     */
    public function store($user_id)
    {

        // Validate Project / User Connection
        $user = User::where('id', $user_id)->select('id')->first();

        if (!$user) {
            return $this->setStatusCode(404)->replyWithError(trans('api.users_errors_404'));
        }

        $user_is_member = $this->compareProjects($user_id, $this->key);

        if (!$user_is_member) {
            return $this->setStatusCode(401)->replyWithError(trans('api.projects_users_not_connected'));
        }

        $name = checkParam('name', true);

        $playlist = Playlist::create([
            'user_id'           => $user_id,
            'name'              => request()->name,
            'featured'          => false
        ]);

        return $this->reply($playlist);
    }

    /**
     * Remove the specified playlist.
     *
     * @OA\Delete(
     *     path="/playlists/{user_id}/{playlist_id}",
     *     tags={"Playlists"},
     *     summary="Delete a playlist",
     *     description="",
     *     operationId="v4_playlists.destroy",
     *     @OA\Parameter(name="user_id", in="path", required=true, @OA\Schema(ref="#/components/schemas/User/properties/id")),
     *     @OA\Parameter(name="playlist_id", in="path", required=true, @OA\Schema(ref="#/components/schemas/Playlist/properties/id")),
     *     @OA\Parameter(ref="#/components/parameters/version_number"),
     *     @OA\Parameter(ref="#/components/parameters/key"),
     *     @OA\Parameter(ref="#/components/parameters/pretty"),
     *     @OA\Parameter(ref="#/components/parameters/format"),
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
     * @param  int $user_id
     * @param  int $playlist_id
     *
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($user_id, $playlist_id)
    {
        // Validate Project / User Connection
        $user = User::where('id', $user_id)->select('id')->first();

        if (!$user) {
            return $this->setStatusCode(404)->replyWithError(trans('api.users_errors_404'));
        }

        $user_is_member = $this->compareProjects($user_id, $this->key);

        if (!$user_is_member) {
            return $this->setStatusCode(401)->replyWithError(trans('api.projects_users_not_connected'));
        }

        $playlist = Playlist::where('user_id', $user_id)->where('id', $playlist_id)->first();

        if (!$playlist) {
            return $this->setStatusCode(404)->replyWithError('Playlist Not Found');
        }

        $playlist->items()->delete();
        $playlist->delete();

        return $this->reply('Playlist Deleted');
    }

    private function validatePlaylist()
    {
        $validator = Validator::make(request()->all(), [
            'user_id'           => 'required|exists:dbp_users.users,id',
            'name'              => 'required|string'
        ]);
        if ($validator->fails()) {
            return ['errors' => $validator->errors()];
        }
        return true;
    }

}