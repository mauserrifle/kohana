<?php defined('SYSPATH') or die('No direct script access.');

/**
 * This file demonstrates a behaviour with routes I found in Kohana.
 *
 * First problem
 * -----------
 * I thought it was a bug but found out my param 'version' was defined as
 * a [0-9]++ and the default was set as a string '1'. This causes multiple
 * weird behaviors I will be demonstrating within this Task. Not really a bug
 * as its fixed with the numeric default value. Still worth to look at.
 *
 * Maybe a idea to check if a string is numeric?
 *
 * Second problem
 * ----------
 * Ok this problem is really bugging me atm with lots of routes in my projects.
 * Optional params eventually will become required somehow. This is also demon
 * strated within this task
 *
 *
 * Everything is OK on commit 4d17878d08a6c139c615e19cb47eae7dd78820df.
 * Some things are NOTOK on commit fb062cfe4f0bae498ab09a383fb5901bd8563528
 *
 *
 *
 */
class Task_Routebug extends Minion_Task
{
	protected function _execute(array $config)
	{

		Minion_CLI::write('Problem 1');
		Minion_CLI::write('--------');

		/////////////////// First problem //////////////////////

		// Bugged with default version as string
		Route::set('api', 'api(/<version>)/<controller>(/<id>)(.<format>)',
				array (
						'version'     => '[0-9]++',
						'id'          => '[0-9;]++',
				))
				->defaults(array(
						'version'    => '1'
					));





		// First test (OK)
		$uri = Route::get('api')->uri(array(
				'controller' => 'something'
		));

		if ($uri === 'api/something')
			Minion_CLI::write('Test 1     OK           '.$uri);
		else
			Minion_CLI::write('Test 1     NOTOK        '.$uri);





		// Second test (Exception: Kohana_Exception [ 0 ]: Required route parameter not passed: id)
		try
		{
			$uri = Route::get('api')->uri(array(
					'version'    => 1,
					'controller' => 'something'
			));
			Minion_CLI::write('Test 2     OK           '.$uri);
		}
		catch (Exception $ex)
		{
			Minion_CLI::write('Test 2     NOTOK        Exception: '.$ex->getMessage());
		}




		// Set extra default to fix exception
		Route::get('api')->defaults(array(
			'version'    => '1',
			'id'         => FALSE,  // NULL is not excepted and results in exception too
			'format'     => FALSE,  // NULL is not excepted and results in exception too
		));

		// Third test (has a dot at the end of the uri)
		$uri = Route::get('api')->uri(array(
				'version'    => 1,
				'controller' => 'something'
		));

		if ($uri === 'api/1/something')
			Minion_CLI::write('Test 3     OK           '.$uri);
		else
			Minion_CLI::write('Test 3     NOTOK        '.$uri);




		// Set version default to int (as it should)
		Route::get('api')->defaults(array(
				'version'    => 1
		));

		// Forth test (ALL fixed now!)
		$uri = Route::get('api')->uri(array(
				'version'    => 1,
				'controller' => 'something'
		));
		Minion_CLI::write('Test 4     OK           '.$uri);


		/////////////////// Second problem //////////////////////

		// Optional param becomes required


		Minion_CLI::write('Problem 2');
		Minion_CLI::write('--------');

		// First test (Kohana_Exception [ 0 ]: Required route parameter not passed: id)
		try
		{
			Route::set('adverts','(<lang>/)<network>/adverts(/<id>)/<action>',
					array(
							'lang'        => '[a-z]{0,3}',
							'id'          => '[0-9]++',
					))
					->defaults(array(
							'directory'  => 'Site',
							'controller' => 'Advert'
					));

			$uri = Route::get('adverts')->uri(array(
				'action'  => 'place',
				'lang'    => 'en',
				'network' => 'earth'
			));

			Minion_CLI::write('Test 1     OK          '.$uri);
		}
		catch (Exception $ex)
		{
			Minion_CLI::write('Test 1     NOT OK      Exception: '.$ex->getMessage());
		}

	}
}