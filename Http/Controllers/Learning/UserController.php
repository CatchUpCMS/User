<?php namespace Modules\User\Http\Controllers\Learning;

use Modules\Dashboard\Repositories\CountryRepository;
use Modules\User\Http\Requests\Learning\UpdateProfileRequest;
use Modules\User\Repositories\Criterias\OrderBy;
use Modules\User\Repositories\UserRepository;
use Pingpong\Modules\Routing\Controller;
use Modules\User\Polices\Learning\UserPolice;


class UserController extends Controller {

	/**
	 * @var UserRepository
	 */
	private $user;
	/**
	 * @var UserPolice
	 */
	private $userPolice;
	/**
	 * @var CountryRepository
	 */
	private $country;

	function __construct(UserRepository $user, CountryRepository $country, UserPolice $userPolice)
	{

		$this->user = $user;
		$this->userPolice = $userPolice;
		$this->country = $country;
	}


	public function getProfile($slugOrId)
	{
		$profile = $this->user->findBySlugOrIdOrFail($slugOrId);
		$updatePolice = $this->userPolice->update($profile);
		$countries = $this->country->all(['id', 'short_name'])->lists('short_name', 'id');
		return theme('user.learning.profile', compact('profile', 'updatePolice', 'countries'));
	}

	public function getPublicProfile($slugOrId)
	{
		$profile = $this->user->findBySlugOrIdOrFail($slugOrId);

		return theme('user.public.profile', compact('profile'));
	}

	/**
	 * @param $slugOrId
	 * @param UpdateProfileRequest $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
	public function putProfile($slugOrId, UpdateProfileRequest $request)
	{
		$user = $this->user->findBySlugOrIdOrFail($slugOrId);
		$user->update($request->all());

		return redirect(route('learning.user.profile', $user->slug));
	}

	public function getRanking($iso = false)
	{
		$this->user->pushCriteria(new OrderBy('points'));
		if(!$iso)
		{
			$users = $this->user->all()->take(32);
			$title = trans('user::ui.ranking.global');
		} else {
			$country = $this->country->findByField('iso2', $iso)->first();
			$users = $this->user->findWhere(['country_id' => $country->id]);
			$title = trans('user::ui.ranking.country', ['country'=>$country->short_name]);
		}
		return theme('user.learning.ranking', compact('users', 'title'));
	}
}