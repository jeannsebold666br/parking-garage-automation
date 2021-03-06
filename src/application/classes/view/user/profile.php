<?php defined('SYSPATH') or die('No direct script access.');
/**
 * User profile view.
 *
 * @package   Park-a-Lot
 * @category  View
 * @author    Abdul Hassan
 * @copyright (c) 2011 The authors
 * @license   see LICENSE
 */
class View_User_Profile extends View_Base
{
	public $styles = array(
		array(
			'href'  => 'media/css/calendar.css',
			'media' => 'all',
		),
	);
	
	public function title()
	{
		return $this->full_name();
	}

	public function full_name()
	{
		return $this->user->first_name.' '.$this->user->last_name;
	}

	/**
	 * Returns data about the logged in user.
	 *
	 * @return array
	 */
	public function user()
	{
		if ($this->user instanceof ORM)
		{
			return array(
				'first_name' => $this->user->first_name,
				'last_name'  => $this->user->last_name,
				'registration_date'  => date('M g, Y', $this->user->registration_date),
				'total_reservations' => $this->user->reservations->count_all(),
				'current_bill' => number_format($this->user->current_bill(), 2),
			);
		}
	}

	/**
	 * Returns a calendar(s) of reservations.
	 *
	 * @return string
	 */
	public function calendar()
	{
		$reservations = array(
			'this_month' => $this->user->reservations
				->where('start_time', '>', time())
				->where('start_time', '<', mktime(24, 0, 0, date('n') + 1, 0))
				->where('active', '=', TRUE)
				->find_all(),
			'next_month' => $this->user->reservations
				->where('start_time', '>', mktime(24, 0, 0, date('n') + 1, 0))
				->where('start_time', '<', mktime(24, 0, 0, date('n') + 2, 0))
				->where('active', '=', TRUE)
				->find_all(),
		);

		return $this->reservations_by_month($reservations['this_month'], date('n'))
			. $this->reservations_by_month($reservations['next_month'], date('n') + 1);
	}

	/**
	 * Plots a calendar full of reservations for a given month.
	 *
	 * @param  MySQL_Result
	 * @param  int
	 * @return string
	 */
	public function reservations_by_month($reservations, $month)
	{
		$days = array();

		foreach ($reservations as $reservation)
		{
			$day = date('j', $reservation->start_time);

			$days[$day] = array(
				0 => 'reservation/list/'.mktime(0, 0, 0, $month, $day), // Where to link to
				1 => 'event', // Classname to add to day cell
			);
		}

		if (date('M j Y') === date('M j Y', mktime(0, 0, 0, $month)))
		{
			// Highlight todays date
			$days[date('j')][1] = 'today';
		}

		return Date::calendar(date('Y'), $month, $days);
	}

	/**
	 * Adds notifications to the template.
	 *
	 * @return string
	 */
	public function render()
	{
		$this->partial('calendar_reservations', 'partials/calendar_reservations');

		if (Session::instance()->get_once(Session::NEW_USER))
		{
			$this->notifications[] = 'Welcome to Park a Lot, the answer to your
				parking needs. Check out our '.HTML::anchor(NULL, 'FAQ');
		}

		if (Session::instance()->get_once(Session::NEW_RESERVATION))
		{
			$this->notifications[] = 'Your reservation has been created successfully.';
		}

		if (Session::instance()->get_once(Session::EDIT_RESERVATION))
		{
			$this->notifications[] = 'Your reservation has been edited successfully.';
		}

		if (Session::instance()->get_once(Session::CANCEL_RESERVATION))
		{
			$this->notifications[] = 'Your reservation has been cancelled successfully.';
		}

		if (Session::instance()->get_once(Session::NEW_VEHICLE))
		{
			$this->notifications[] = 'Your vehicle has been added successfully.';
		}

		if (Session::instance()->get_once(Session::REMOVE_VEHICLE))
		{
			$this->notifications[] = 'Your vehicle has been removed successfully.';
		}

		if (Session::instance()->get_once(Session::SUCCESSFUL_RESEND_USER_CONFIRMATION))
		{
			$this->notifications[] = 'Confirmation instructions have been sent to your email address.';
		}
		else if (Session::instance()->get_once(Session::FAILED_RESEND_USER_CONFIRMATION))
		{
			$this->notifications[] = 'Sorry, we could not send confirmation instructions to you at this time.';
		}

		if (Session::instance()->get_once(Session::SUCCESSFUL_USER_CONFIRMATION))
		{
			$this->notifications[] = 'Your account is now confirmed.';
		}
		else if (Session::instance()->get_once(Session::FAILED_USER_CONFIRMATION))
		{
			$this->notifications[] = 'Sorry, we could not confirm your account. Please be sure follow the directions we sent you.';
		}

		if (Session::instance()->get_once(Session::PRICE_PLAN_ACTIVATED))
		{
			$this->notifications[] = 'Price plan has been activated successfully.';
		}

		return parent::render();
	}
} // End View_User_Profile