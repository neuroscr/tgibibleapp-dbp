@extends('layouts.app')

@section('head')
    <title>{{ $user->name }}'s Profile</title>
@endsection

@section('content')
	<div class="container">

				<div class="card">
					<div class="card-header">{{ trans('profile.showProfileTitle', ['username' => $user->name]) }}</div>
					<div class="card-body">

						<dl class="user-info">
							<dt>{{ trans('profile.showProfileUsername') }}</dt>
							<dd>{{ $user->name }}</dd>

							<dt>{{ trans('profile.showProfileFirstName') }}</dt>
							<dd>{{ $user->first_name }}</dd>

							@if($user->last_name)
								<dt>{{ trans('profile.showProfileLastName') }}</dt>
								<dd>{{ $user->last_name }}</dd>
							@endif

							<dt>{{ trans('profile.showProfileEmail') }}</dt>
							<dd>{{ $user->email }}</dd>

							@if ($user->profile)

								@if ($user->profile->location)
									<dt>{{ trans('profile.showProfileLocation') }}</dt>
									<dd>{{ $user->profile->location }} <br />

										@if(config('settings.googleMapsAPIStatus'))
											Latitude: <span id="latitude"></span> / Longitude: <span id="longitude"></span> <br />

											<div id="map-canvas"></div>
										@endif</dd>
								@endif

								@if ($user->profile->bio)
									<dt>{{ trans('profile.showProfileBio') }}</dt>
									<dd>{{ $user->profile->bio }}</dd>
								@endif

								@if ($user->profile->twitter_username)
									<dt>{{ trans('profile.showProfileTwitterUsername') }}</dt>
									<dd>{!! HTML::link('https://twitter.com/'.$user->profile->twitter_username, $user->profile->twitter_username, array('class' => 'twitter-link', 'target' => '_blank')) !!}</dd>
								@endif

								@if ($user->profile->github_username)
									<dt>{{ trans('profile.showProfileGitHubUsername') }}</dt>
									<dd>{!! HTML::link('https://github.com/'.$user->profile->github_username, $user->profile->github_username, array('class' => 'github-link', 'target' => '_blank')) !!}</dd>
								@endif
							@endif

						</dl>

						@if ($user->profile)
							@if (Auth::user()->id == $user->id)

								{!! HTML::icon_link(URL::to('/profile/'.Auth::user()->name.'/edit'), 'fa fa-fw fa-cog', trans('titles.editProfile'), array('class' => 'btn btn-small btn-info btn-block')) !!}

							@endif
						@else

							<p>{{ trans('profile.noProfileYet') }}</p>
							{!! HTML::icon_link(URL::to('/profile/'.Auth::user()->name.'/edit'), 'fa fa-fw fa-plus ', trans('titles.createProfile'), array('class' => 'btn btn-small btn-info btn-block')) !!}

						@endif

					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('footer_scripts')

	@if(config('settings.googleMapsAPIStatus'))
		@include('scripts.google-maps-geocode-and-map')
	@endif

@endsection