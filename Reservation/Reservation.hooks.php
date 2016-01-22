<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Owen Kellie-Smith
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * @file
 */


class ReservationHooks {

	public static function mySchemaUpdate(DatabaseUpdater $updater ) {
		$updater->addExtensionTable( 'tablename',
			__DIR__ . '/table.sql' );
		return true;
	}

	public static function onUnitTestsList( &$files ) {
		$files = array_merge( $files, glob( __DIR__ . '/tests/phpunit/*Test.php' ) );
		return true;
	}


	public static function wfPrefHook( $user, &$preferences ) {
		// A set of radio buttons. Notice that in the 'options' array,
		// the keys are the text (not system messages), and the values are the HTML values.
		// They keys/values might be the opposite of what you expect. PHP's array_flip()
		// can be helpful here.
	$preferences['myResPref'] = array(
		'type' => 'radio',
		'label-message' => 'tog-blades', // a system message
		'section' => 'personal/info',
		// Array of options. Key = text to display. Value = HTML <option> value.
		'options' => array(
			'Show fixed times e.g. 10:00' => 'choiceResFix',
			'Show relative periods e.g. 1 hour' => 'choiceResRel',
		),
		'default' => 'choiceResRel',  // A 'default' key is required!
		'help-message' => 'tog-help-blades', // a system message (optional)
	);

		// Required return value of a hook function.
	return true;
}



}
