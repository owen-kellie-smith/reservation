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
 * @author     Owen Kellie-Smith
 */


class ReservationRender  {

private $fillerForEmptyCell = "";

public function __construct(ReservationObject $obj=null){
	$this->eqref=(int)mt_rand();
}

	/**
	 * Get rendering of whole unrendered result as array of strings 
	 * that can be echoed to screen
	 *
	 * @param array $u result parameters
	 * @return array
	 *
	 * @access public
	 */
public function get_rendered_result( $u=array(), $pageTitle='' ){
		$r = array();
			if (isset($u['table'])){
       	if (isset($u['table']['schedule'])){
           $r['schedule'] = $this->get_table(
           		$u['table']['schedule']['data'],
           		$u['table']['schedule']['header']
             );
          }
       	if (isset($u['table']['all-blades'])){
           $r['all-blades'] = $this->get_table(
           		$u['table']['all-blades']['data'],
           		$u['table']['all-blades']['header']
             );
          }
       	if (isset($u['table']['your-blades'])){
           $r['your-blades'] = $this->get_table(
           		$u['table']['your-blades']['data'],
           		$u['table']['your-blades']['header']
             );
          }
       	if (isset($u['table']['current-usage'])){
           $r['current-usage'] = $this->get_table(
           		$u['table']['current-usage']['data'],
           		$u['table']['current-usage']['header']
             );
          }
       	if (isset($u['table']['usage'])){
           $r['usage'] = $this->get_table(
           		$u['table']['usage']['data'],
           		$u['table']['usage']['header']
             );
          }
       	if (isset($u['table']['log'])){
           $r['log'] = $this->get_table(
           		$u['table']['log']['data'],
           		$u['table']['log']['header']
             );
          }
       	if (isset($u['table']['bookings'])){
           $r['bookings'] = $this->get_table(
           		$u['table']['bookings']['data'],
           		$u['table']['bookings']['header'],
			$u['table']['bookings']['delete'],
			$u['table']['bookings']['deleteColumn'],
			$u['table']['bookings']['deleteField']
             );
          }
       	if (isset($u['table']['immediate'])){
           $r['immediate'] = $this->get_table(
           		$u['table']['immediate']['data'],
           		$u['table']['immediate']['header']
             );
          }
				if (isset($u['table']['rates']) && isset($u['table']['hidden'])){
				 	$r['table'] = $this->get_render_rate_table(
						$u['table']['rates'],
						$u['table']['hidden'], $pageTitle . "?" 
					);    
				}
			}
			if (isset($u['forms'])){
				foreach ($u['forms'] AS $_f){
					try{	
						$r['forms'][] = $this->get_render_form($_f['content'], $_f['type'] ); 
					} catch( Exception $e ){
						$r['forms'][] = $e->getMessage() ;
					}
				}
			}
	return $r;
}


	/**
	 * Get rendering of form as string that can be echoed to screen
	 *
	 * @param array $form form parameters
	 * @return string
	 *
	 * @access private
	 */
	private function get_render_form( $form, $type='' ){
		if (isset($form['render'])){
			if ('plain'==$form['render'] ){
			  return $this->get_form_plain( $form );
			}
		} // else  default is html
			if ( 'get_form_collection'==$type ){
				return $this->get_form_collection( $form['collection'], $form['submit'], $form['intro'], $form['request']);
			} elseif ( 'select'==$type ){
				return $this->get_select_form( $form );
			} else {
				return $this->get_form_html( $form );
		  }
	}


/*
	private function get_render_rate_table( $rates, $hidden, $link='' ){
		$link .=  $this->get_link($hidden);
		for ( $i = 0, $ii = count( $rates['data'] ); $i < $ii; $i++ ){
			$f = $rates['objects'][$i]['ReservationForwardRate'];
			$p = null;
			if (isset($rates['objects'][$i]['ReservationParYield'])){
				$p = $rates['objects'][$i]['ReservationParYield'];
			}
			if ( is_object( $f ) ){
				$rates['data'][$i][3]  = $this->get_anchor_forward( $f, $link );
			}
			if ( is_object( $p ) ){
				$rates['data'][$i][5]  = $this->get_anchor_par( $p, $link );
			}
		}
		return $this->get_table( $rates['data'], $rates['header'] );
	}
*/

	/**
	 * Get string of &,= pairs suitable for writing a URL that can ge read via request
	 *
	 * @param array $hidden list of hidden fields
	 * @return string
	 *
	 * @access private
	 */
/*	private function get_link( $hidden ){
		$out = "";
		if (count(array_keys( $hidden)) > 0 ){
			foreach(array_keys( $hidden) as $key ){
				$value = $hidden[$key];
				$out .= "&" . $key . "=" . $value;
			}
		}
		return $out;
	}
*/

	/**
	 * Get rendering of form as string.  Form includes as hidden fields all the features of a collection
	 *
	 * @param ReservationCollection $cf collection of objects
	 * @param string $submit text of submit button
	 * @param string $intro text of sentence (if any) to put at top of form.
	 * @param string $request value to include as hidden field of form (which passes form's main commend)
	 * @return string
	 *
	 * @access private
	 */
/*
	private function get_form_collection( ReservationCollection $cf, $submit = 'Submit', $intro = "" , $request = "", $pageid=""){
		$out = "";
		if ( !empty( $intro ) ){
			$out.= "<p>" . $intro . "</p>" . "\r\n";
		}
		$form = new HTML_QuickForm2( 'name-for-collection','GET', '');
		$form->addDataSource(new HTML_QuickForm2_DataSource_Array() );
		$fieldset = $form->addElement('fieldset');
		$hidden = $cf->get_values_as_array(  get_class( $cf ) );
		$this->add_hidden_fields_to_fieldset( $fieldset, $hidden );
		$fieldset->addElement('hidden', 'request')->setValue( $request );
		if (!empty($pageid)){
			$fieldset->addElement('hidden', 'page_id')->setValue($pageid);
		}
		$fieldset->addElement('submit', null, array('value' => $submit));
		$out.= $form;
		return $out;
	}
*/

	/**
	 * Get HTML table (as string that can be echoed)
	 *
	 * @param array $row_data (to appear in rows of table)
	 * @param array $column_headers (to appear in head of table
	 * @return string
	 *
	 * @access private
	 */
	private function get_table( $row_data, $column_headers , $delete=false, $deleteColumn=-999, $deleteField='dummy-field-name'){

		// see http://pear.php.net/manual/en/package.html.html-table.intro.php
		$table = new HTML_Table();
		$table->setAutoGrow(true);
		$table->setAutoFill('n/a');
		for ( $nr = 0, $maxr = count( $row_data ); $nr < $maxr; $nr++ ){
			for ($i =0, $ii = count( $column_headers ); $i < $ii; $i++ ){
				if (isset($row_data[$nr][$i] )){
					if ('' != $row_data[$nr][$i] ){
						if ($deleteColumn == $i ){
							if ($delete){
								$table->setCellContents( $nr+1, $i,  $this->getDeleteButton( $row_data[$nr][$i], $deleteField) );
							} 
						} else {
							$table->setCellContents( $nr+1, $i, $row_data[$nr][$i] );
						}
					}
				} else {
					$table->setCellContents( $nr+1, $i, $this->fillerForEmptyCell );
				}
			}
		}
		for ($i =0, $ii = count( $column_headers ); $i < $ii; $i++ ){
			$table->setHeaderContents(0, $i , $column_headers[ $i ]);
		}
		$header_attribute = array( 'class' => 'header' );
		$table->setRowAttributes(0, $header_attribute, true);
		$table->setColAttributes(0, $header_attribute);
		$altRow = array( 'class' => 'alt_row' );
		$table->altRowAttributes( 1, null, $altRow );
		return $table->toHtml();
	}

	/**
	 * Get (in form of string) html for form which has just <select> elements
	 *
	 * @param array $return form features
	 * @return string
	 *
	 * @access private
	 */
	private function get_select_form( $return ){
		$out="";
		if ( !empty( $return['introduction'] ) ){
			$out = "<p>" . $return['introduction'] . "</p>" . "\r\n";
		}
		foreach (array('name','method','action','select','submit', 'order','formLabel') as $key){
			$temp[$key]='';
			if ( isset( $return[$key] ) ){
				$temp[$key] = $return[$key];
			}
		}
		$return = $temp; $temp=null;
		$form = new HTML_QuickForm2($return['name'],$return['method'], $return['action']);
		$fs = $form->addElement('fieldset')->setLabel($return['formLabel']);
		$fieldset = $fs->addElement('group');
      		foreach ($return['select'] as $s){
			$calculator = $fieldset->addSelect( $s['select-name'] )
				->setLabel( $s['select-label'] )
				->loadOptions( $s['select-options']);
		}
		$temp_page_id='';
		if( isset($return['page_id'])){
			$temp_page_id = $return['page_id'];
		}
		$fieldset->addElement('hidden', 'page_id')->setValue($temp_page_id);
		if ( isset( $return['order'] ) ){
			$fieldset->addElement('hidden', 'order')->setValue($return['order']);
		}
		$temp_page_id= null;
		$fs->addElement('submit', null, array('value' => $return['submit']));
		$out.= $form;
		$form = null;
		$fieldset = null;
		return $out;
	}

	protected static function myMessage( $messageKey){
			$m = $messageKey;
			if ( function_exists('wfMessage') ){
				$m=wfMessage( $messageKey)->text();
			}
			return $m;
  }
			

	/**
	 * Get anchor to detailed forward rate calculation
	 *
	 * @param ReservationForwardRate $f
	 * @param string $page_link
	 * @return string
	 *
	 * @access private
	 */
/*
	private function get_anchor_forward( ReservationForwardRate $f, $page_link ){
		return "<a href='" . $page_link . "&request=explain_forward&forward_start_time=" . $f->get_start_time() . "&forward_end_time=" . $f->get_end_time() . "'>" . $f->get_i_effective() . "</a>";
	}
*/
	

	/**
	 * Get form rendered as html
	 *
	 * @param array $return  form parameters
	 * @return string
	 *
	 * @access private
	 */
	private function get_form_html( $return ){
		// returns html based on form parameters in $return
		$fieldset=null;;
		$out = "<p>" . $return['introduction'] . "</p>" . "\r\n";
		if (!isset($return['name'])){
			$return['name']='';
		}
		if (!isset($return['action'])){
			$return['action']='';
		}
		$form = new HTML_QuickForm2($return['name'],$return['method'], $return['action']);
		if (!isset($return['values'])){
			$return['values']=array();
		}
		$form->addDataSource(new HTML_QuickForm2_DataSource_Array( $return['values'] ) );
		if (count($return['parameters']) > 0){
			$fieldset = $form->addElement('fieldset');
			foreach(array_keys($return['parameters']) as $key){
				$parameter = null;
				$valid_option=null;
				$input_type=null;
				if (isset($return['exclude'])){
				  if (!in_array($key, $return['exclude'])){
					$parameter = $return['parameters'][$key];
					$valid_option = array();
					if (array_key_exists($key,$return['valid_options'])){
						$valid_option = $return['valid_options'][$key];
						if ('string'==$valid_option['type']){ 
							$input_type='textarea';
						}
						if ('number'==$valid_option['type']){ 
							$input_type='text';
						}
						if ('boolean'==$valid_option['type']){ 
							$input_type='checkbox';
						}
					}
					$value = '';
					$fieldset->addElement($input_type, $key)->setLabel($parameter['label']);
				  }
				}
			}
		}
		if (isset($return['hidden'])){
		  if (count($return['hidden']) > 0){
			$fieldset_hidden = $form->addElement('fieldset');
			foreach(array_keys( $return['hidden']) as $key ){
				$value = $return['hidden'][$key];
				$fieldset_hidden->addElement('hidden', $key)->setValue( $value );
			}
		  }
		}
		// add page_id
		if ($fieldset){
			$fieldset->addElement('hidden', 'request')->setValue($return['request']);
			if (isset($return['page_id'])){
				$fieldset->addElement('hidden', 'page_id')->setValue($return['page_id']);
			}
			$fieldset->addElement('submit', null, array('value' => $return['submit']));
		}
		$out.= $form;
		return $out;
	}

	/**
	 * Modify (input) fieldset so it includes hidden fields
	 *
	 * @param fieldset $fieldset HTML/QuickForm2 fieldset to modify
 	 * @param array $hidden array of hidden fieldnames (keys) and values
	 * @return null
 	 *
	 * @access private
	 */
	private function add_hidden_fields_to_fieldset( &$fieldset, $hidden ){
		foreach(array_keys( $hidden) as $key ){
			$value = $hidden[$key];
			$fieldset->addElement('hidden', $key)->setValue( $value );
		}
	}

	private function getDeleteButton( $id, $fieldName ){
		$form = new HTML_QuickForm2('DeleteButton', 'post', array(
    'action' => $_SERVER['PHP_SELF']));
		$form->addElement('hidden', $fieldName)->setValue($id);
		$form->addElement('hidden', 'order')->setValue('cancel_booking');
		$form->addElement('submit', null, array('value' => wfMessage('reservation-label-cancel')->text()));
		return $form;
	}



} // end of class

