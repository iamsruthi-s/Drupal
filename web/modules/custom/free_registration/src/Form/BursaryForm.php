<?php

namespace Drupal\free_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\views\Views;
use Drupal\media\Entity\Media;

class BursaryForm extends FormBase {
  public function getFormId() {
    // Here we set a unique form id
    return 'simple_custom_form';
  }

public function buildForm(array $form, FormStateInterface $form_state, $c_id = NULL,$status = NULL) {


$tempstore = \Drupal::service('tempstore.private')->get('free_registration');

//$param=$status=null;
$form['#attributes']['autocomplete'] = 'off';
$param = \Drupal::request()->query->all();
$status_val=$status;
$status='';

if ($status_val)
		{

			 $disabled=array('disabled'=>'disabled');
			  $stats_val=$param['stat'];
			  if($status_val=='initiate')
			  {
				  	$first_name=$last_name=$email='';
				  	$status=0;
				  	 $tempstore->set('otp', FALSE);
					 $tempstore->set('first_name', FALSE);
					 $tempstore->set('last_name',FALSE);
					 $tempstore->set('email', FALSE);

					 $tempstore->delete('otp');
					 $tempstore->delete('first_name');
					 $tempstore->delete('last_name');
					 $tempstore->delete('email');
					 \Drupal::service('config.factory')->getEditable('free_registration.settings')->delete();

				}
			  else if($status_val=='verify')
			  {

					$config = \Drupal::service('config.factory')->getEditable('free_registration.settings'); // add the cache tag, so that the output gets invalidated when the config is saved

				  	 $otp = $tempstore->get('otp');
					 $first_name = $tempstore->get('first_name');
					 $last_name = $tempstore->get('last_name');
					 $email = $tempstore->get('email');
					$status=1;
			  }

			  else if($status_val=='final')
			  {

					$config = \Drupal::service('config.factory')->getEditable('free_registration.settings'); // add the cache tag, so that the output gets invalidated when the config is saved

				  	 $otp = $tempstore->get('otp');
					 $first_name = $tempstore->get('first_name');
					 $last_name = $tempstore->get('last_name');
					 $email = $tempstore->get('email');
				     $status=2;
				     $tempstore->set('otp', FALSE);
				     $tempstore->set('first_name', FALSE);
				     $tempstore->set('last_name',FALSE);
				     $tempstore->set('email', FALSE);

				     $tempstore->delete('otp');
				     $tempstore->delete('first_name');
				     $tempstore->delete('last_name');
				     $tempstore->delete('email');
			  }


		}
		else
		{


			$status=0;
			$email=$last_name=$first_name='';
			$disabled='';
			$tempstore->delete('otp');
		    $tempstore->delete('first_name');
			$tempstore->delete('last_name');
			$tempstore->delete('email');
		}


  $form['conference'] = [
    '#type' => 'textfield',
    '#title' => 'Conference',
     '#value' => $c_id,
    '#required' => TRUE,
    //'#access'=>False,
    '#attributes' => array('class' => array('hidden conference')),
  ];

  $form['first_name'] = [
    '#type' => 'textfield',
    '#title' => 'First Name',
    '#default_value'=>$first_name,
    '#required' => TRUE,
     '#attributes' => array('class' => array('first_name'),$disabled),
  ];

  $form['last_name'] = [
    '#type' => 'textfield',
    '#title' => 'Last Name',
    '#required' => TRUE,
     '#default_value'=>$last_name,
     '#attributes' => array('class' => array('last_name'),$disabled),
  ];

  $form['email'] = [
    '#type' => 'email',
    '#title' => 'Email',
	'#pattern' => '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$',
     '#default_value'=>$email,
    '#required' => TRUE,
    '#attributes' => array('class' => array('email'),$disabled),
  ];

 $form['stat'] = [
    '#type' => 'textfield',
    '#title' => 'stat',
    '#required' => TRUE,
   '#access'=>False,
    '#value'=> $status,
    '#attributes' => array('class' => array('email')),
  ];

 /* $form['verify'] = array(
				'#prefix' => '
				<div class="col-lg-12 col-md-12 col-sm-12 col-12">
				<div class="conference-steps">
				<button type="button"  class="btn btn-primary btn-download"><a class="button button--primary btn-success btn icon-before verify_pre_reg" download>Submit</a></button>
				</div>
				</div>

						' );

*/


	if($status_val=='verify')
	{
		$form['opt_hdn'] = array(
						'#prefix' => '
						<div class="col-lg-12 col-md-12 col-sm-12 col-12">
						<div class="otp_hidden">

						</div>
						</div>

								',
							'#access'=>False,
								 );

		 $form['opt'] = [
			'#type' => 'textfield',
			'#title' => 'An OTP has been sent to your email address for verification. Please check your junk/spam folder if not found in your inbox',
			'#prefix' => '<div class="verify_data">',
			'#required' => TRUE,
			 '#attributes' => array('class' => array('ent_otp')),
			 '#help_text' => '',
		  ];
		  $form['opt']['#attributes']['placeholder'][] = t('Enter OTP');

		  $form['otp_help'] = array(
						'#prefix' => '
						<div class="col-lg-12 col-md-12 col-sm-12 col-12">
						<div class="otp_help">
						<span class="otp_help verify_pre_reg resend_otp" style="cursor: pointer; color: #337ab7;text-decoration: none;">Click here</span>to resend OTP
						</div>
						</div></div>' );

  }
 elseif($status_val=='final') {

		 $form['title_user'] = [
			'#type' => 'textfield',
			'#title' => 'Designation Title',
			'#prefix' => '<div class="final_data">',
			 '#attributes' => array('class' => array('title_user')),
			 '#help_text' => '',
		  ];
		  $form['organisation'] = [
			'#type' => 'textfield',
			'#title' => 'Organisation',
			 '#attributes' => array('class' => array('organisation')),
			 '#help_text' => '',
		  ];

		$query = \Drupal::database()->select('node_field_data', 'n');
	    $query->fields('n', ['nid','title']);
	    $query->condition('n.type', "country_profile_management", '=');
	    $query->condition('n.status', "1", '=');
	    $query->leftJoin('node__field_region', 'py', 'n.nid = py.entity_id');

	    $query->orderBy('title', 'ASC');
	    $result = $query->execute();
	    $i=0;
	    while ($row = $result->fetchAssoc()) {
			 $i++;
			$value=$row['nid'];
			$name=$row['title'];
			$array=[];
			$array['index']=$value;
			$array['value']=$name;
		    $region_list[$value]=$name;

		 }

		   $form['nationality']=array(
          '#type' => 'select',
          '#title' => 'Nationality',
          '#multiple' => FALSE,
          '#required' => FALSE,
          '#display' => 'visible',
          '#options' => $region_list,
          '#empty_option' => '-None-',
          '#attributes' => array('class' => array('pull-left'), 'data-placeholder' => 'Select Nationality'));

		  $form['country']=array(
          '#type' => 'select',
          '#title' => 'Country of Residence',
          '#multiple' => FALSE,
          '#required' => TRUE,
          '#display' => 'visible',
          '#options' => $region_list,
          '#empty_option' => '-None-',
          '#attributes' => array('class' => array('pull-left'), 'data-placeholder' => 'Select Country'));

           $vocabulary = 'gender';
$vocabulary_term =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary);
foreach ($vocabulary_term as $vocabulary_terms) {
 $gender_list[$vocabulary_terms->tid] = $vocabulary_terms->name;
}
		$form['gender']=array(
          '#type' => 'radios',
          '#title' => 'Gender',
          '#required' => TRUE,
          '#display' => 'visible',
          '#options' => $gender_list,
          '#empty_option' => '-None-',
          '#attributes' => array('class' => array('pull-left'), 'data-placeholder' => 'Select Gender'));


   $current_path = \Drupal::service('path.current')->getPath();
  $current_path = (explode("/",$current_path));
   $taxoid = $current_path[2];

   $view = Views::getView("dashboard_taxonomy_terms");
    if (is_string("page_4")) {
      $view->setDisplay("page_4");
    }
    else {
      $view->initDisplay();
    }

    $view->setArguments([$taxoid]);
            $view->preExecute();
            $view->execute();
            if (!empty($view->result) && is_iterable($view->result)) {
   foreach ($view->result as $rid => $row) {
              foreach ($view->field as $fid => $field) {
                $values[$rid][$fid] = $field->getValue($row);

              }

            }
}
$abstract_strands_list = [];
if (!empty($values)) {
 foreach($values as $val) {
	 if (isset($val['tid']) && isset($val['name'])) {
	 $abstract_strands_list[$val['tid']] = $val['name'];
}
 }
}

$form['abstract_strands']=array(
          '#type' => 'radios',
          '#title' => 'Abstract Strands',

          '#required' => FALSE,
          '#display' => 'visible',
          '#options' => $abstract_strands_list,

          '#attributes' => array('class' => array('pull-left'), 'data-placeholder' => 'Select Abstract Strands'));


  $language_vocabulary = 'languages';
$language_terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($language_vocabulary);
foreach ($language_terms as $language_term2) {
 $languages_list[$language_term2->tid] = $language_term2->name;
}
         $form['languages']=array(
          '#type' => 'checkboxes',
          '#title' => 'Language to be used for a presentation',

          '#required' => FALSE,
          '#display' => 'visible',
          '#options' => $languages_list,

          '#attributes' => array('class' => array('pull-left'), 'data-placeholder' => 'Select Language'));


//~ $allowed_extensions = ['txt', 'pdf', 'doc', 'docx'];

   	   //~ $form['ctm_file_upload'] = [
			//~ '#type' => 'managed_file',
			  //~ '#title' => t('Upload Abstract'),
			  //~ '#description' => $this->t('Allowed file extensions: @extensions', ['@extensions' => implode(', ', $allowed_extensions)]),
			  //~ '#upload_validators' => [
				//~ 'file_validate_extensions' => $allowed_extensions,
				//~ 'file_validate_size' => [25600000],
			  //~ ],
			  //~ '#upload_location' => 'public://post-file',
			  //~ '#required' => TRUE,
			  //~ '#attributes' => array('class' => array('ctm_file_upload')),
			  //~ '#prefix' => '</div">',
		//~ ];
		
		$allowed_extensions = 'pdf doc docx txt';

   	   $form['ctm_file_upload'] = [
			'#type' => 'managed_file',
			  '#title' => t('Upload Abstract'),
			  '#description' => t('Allowed file extensions: @extensions', ['@extensions' => $allowed_extensions]),
    '#upload_validators' => [
      'file_validate_extensions' => [$allowed_extensions], 
    ],
			  '#upload_location' => 'public://post-file',
			  '#required' => TRUE,
			  '#attributes' => array('class' => array('ctm_file_upload')),
			  '#prefix' => '</div">',
		];


		 $form['verify_otp'] = array(
						'#prefix' => '

						<div class="conference-steps2">
						<button type="button"  class="btn btn-primary btn-download"><a class="button button--primary btn-success btn icon-before verify_otp" download>Submit</a></button>

						</div>' );

	}





  $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
      '#button_type' => 'primary',
    );
$form['#cache'] = ['max-age' => 0];

  return $form;
}

  public function validateForm(array &$form, FormStateInterface $form_state) {
  // Limit text length to 4.

  $tempstore = \Drupal::service('tempstore.private')->get('free_registration');
  $opt = $form_state->getValue('opt');
  $stat = $form_state->getValue('stat');
  $email = $form_state->getValue('email');
  $first_name = $form_state->getValue('first_name');
  $conference = $form_state->getValue('conference');
  // if($stat==0)
 // {
	  	$exists=count(views_get_view_result('bursary_applicants', 'block_1', $conference,$email));
	  	if($exists>0)
	  	{
			$form_state->setErrorByName('email', 'Abstract already Exists for the User');
		}
 // }

  if($stat==1)
  {
	  $otp_old = $tempstore->get('otp');
	  if ($otp_old!=$opt) {
		$form_state->setErrorByName('opt', 'Error in OTP Validation.');
	  }
	  $tempstore->delete('otp');
	}

}

public function submitForm(array &$form, FormStateInterface $form_state) {


	global $base_url;
		$conference=$form_state->getValue('conference');
		$first_name=$form_state->getValue('first_name');
		$last_name=$form_state->getValue('last_name');
		$email=$form_state->getValue('email');
		$organisation=$form_state->getValue('organisation');
		$title_user=$form_state->getValue('title_user');
		$stat=$form_state->getValue('stat');
		$country=$form_state->getValue('country');
		$gender=$form_state->getValue('gender');

     //$gender = $form['gender']['#options'][$gender_key];

		$nationality=$form_state->getValue('nationality');

		$abstract_strands=$form_state->getValue('abstract_strands');



		$languages=$form_state->getValue('languages');



		//$presentation_format=$form['presentation_format']['#options'][$presentation_format_key];

		//~ print_r($abstract_strands);
		//~ print_r($presentation_format);
        \Drupal::service('page_cache_kill_switch')->trigger();



	if($stat==0)
	{
		$tempstore = \Drupal::service('tempstore.private')->get('free_registration');


		  $mailManager = \Drupal::service('plugin.manager.mail');
		  $module = 'custom_email';
		  $key = 'join_request';
		  $langcode = \Drupal::currentUser()->getPreferredLangcode();
		  $send = TRUE;
		  $digits = 5;
		  $otp= rand(pow(10, $digits-1), pow(10, $digits)-1);

		  $tempstore->set('otp', FALSE);
		  $tempstore->set('first_name', FALSE);
		  $tempstore->set('last_name',FALSE);
		  $tempstore->set('email', FALSE);

		  $tempstore->delete('otp');
		  $tempstore->delete('first_name');
		  $tempstore->delete('last_name');
		  $tempstore->delete('email');

		  $tempstore->set('otp', $otp);
		  $tempstore->set('first_name', $first_name);
		  $tempstore->set('last_name', $last_name);
		  $tempstore->set('email', $email);


		  $config = \Drupal::service('config.factory')->getEditable('free_registration.settings'); // add the cache tag, so that the output gets invalidated when the config is saved
		  $config->set('otp', $otp)->save();
		  $config->set('first_name', $first_name)->save();
		  $config->set('last_name', $last_name)->save();
		  $config->set('email', $email)->save();
		 $node = \Drupal\node\Entity\Node::load($conference);

     if ($node->hasField('field_email_logo') && !$node->get('field_email_logo')->isEmpty()) {
    $media_item = $node->get('field_email_logo')->first()->getValue();

    $media_id = $media_item['target_id'];
    $media = \Drupal\media\Entity\Media::load($media_id);
     if ($media) {

      if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
        $image_item = $media->get('field_media_image')->first();
        $file_entity = $image_item->entity;


       $logo = $file_entity->getFileUri();

        $logo = explode("//", $logo);
        $logo = $logo[1];

	}
}
}

		  if (isset($logo) && !empty($logo)) {
$img = $base_url . "/sites/default/files/styles/medium/public/". $logo;
}
else {
	$img = $base_url . "/sites/default/files/NEC_2022_email_banner_image.png";
}

		  $to = $email;
		  $params['subject'] ='Call for Abstract OTP';
		  $params['message'] = ' <img class="" src="'. $img .'" alt="user img" style="outline:none;text-decoration:none;box-sizing:border-box;max-width:100%;border:none;text-align:center;color:#ff6f6f;font-weight:700;border-radius:5px">';

		  $params['message'] .='<p style="font-size: 14px;">Dear User,</p>';
		  $params['message'] .='<p style="font-size: 14px;"> Your  OTP for Call for Abstract is '. $otp.' </p>';
		  $params['message'] .='<p style="font-size: 14px;">The OTP is confidential and for security reasons, DO NOT share the OTP with anyone. </p>';

		  $params['message'] .='<table style="font-size: 14px; margin-top:40px;"><tr><td><p style="font-size: 14px; margin:0px"><strong style="color: #15c;">NEC Team</strong></p></td></tr>
			<tr><td style="color: #554848;"><p style="font-size: 14px; margin:0px">Independent Evaluation Office</p></td></tr>
			<tr><td style="color: #554848;"><p style="font-size: 14px; margin:0px">UNDP</p></td></tr>
			</table>';
		  $to=$email;

		  if (!empty($to)) {
			$result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
		 if ($result['result'] != TRUE) {

			 $message = t('There was a problem sending your email notification to @email.', ['@email' => $to]);
			  \Drupal::messenger()->addMessage($message, 'error');
			  \Drupal::logger('mail-log')->error($message);
			 // return;

		  }
		  elseif ($result['result'] == TRUE) {
			 $message = t('OTP has been sent to '.$email);
			 \Drupal::messenger()->addMessage($message);
			 \Drupal::logger('mail-log')->notice($message);
		  }

		}





	}
	else if($stat==2)
	{


 $conference_node = \Drupal\node\Entity\Node::load($conference);
     $conference_title=$conference_node->label();

		//~ print_r($gender);echo "<br>";
		//~ print_r($abstract_strands);echo "<br>";
			//~ print_r($languages);echo "<br>";
		//~ exit;
		$participant_countries = [
			['target_id' => $country],
			// Add more countries as needed
		  ];
		  $node = Node::create(array(
		  'type' => 'free_registration',
		  'title' => $first_name." ".$last_name,
		  'field_first_name' => $first_name,
		  'field_la'=>$last_name,
		  'field_email'=>$email,
		  'field_conference'=>$conference,
		  'field_organisation'=>$organisation,
		  'field_title'=>$title_user,
		  'field_country_of_residence'=>$country,
		  'field_gender'=>$gender,
		  'field_nationality'=>$nationality,
		  'field_abstract_strands'=>$abstract_strands,
		  'field_participant_countries' =>$participant_countries,

		  'field_language'=>$languages,

		  ));

	$fid = $form_state->getValue('ctm_file_upload')[0];
	if (isset($fid[0]) && !empty($fid[0])) {
		$file = File::load($fid);
		$file_id = $file->id();
		$file->setPermanent();
		$file->save();
	    $node->set('field_abstract' , ['target_id' => $file_id]);
	}
	 $node->save();
	// drupal_set_message($this->t('Your Abstract has been uploaded!'));

	 $tempstore = \Drupal::service('tempstore.private')->get('free_registration');

	 $tempstore->set('otp', FALSE);
	 $tempstore->set('first_name', FALSE);
	 $tempstore->set('last_name',FALSE);
	 $tempstore->set('email', FALSE);

	 $tempstore->delete('otp');
	 $tempstore->delete('first_name');
	 $tempstore->delete('last_name');
	 $tempstore->delete('email');

	   \Drupal::service('config.factory')->getEditable('free_registration.settings')->delete();
	 $mailManager = \Drupal::service('plugin.manager.mail');
	 $module = 'custom_email';
	 $key = 'join_request';
	 $langcode = \Drupal::currentUser()->getPreferredLangcode();
	 $send = TRUE;
	 $to = $email;
	 //~ $node = \Drupal\node\Entity\Node::load($conference);
     //~ $title=$node->label();
      $node = \Drupal\node\Entity\Node::load($conference);

     if ($node->hasField('field_email_logo') && !$node->get('field_email_logo')->isEmpty()) {
    $media_item = $node->get('field_email_logo')->first()->getValue();

    $media_id = $media_item['target_id'];
    $media = \Drupal\media\Entity\Media::load($media_id);
     if ($media) {
      if ($media->hasField('field_media_image') && !$media->get('field_media_image')->isEmpty()) {
        $image_item = $media->get('field_media_image')->first();
        $file_entity = $image_item->entity;
       $logo = $file_entity->getFileUri();
        $logo = explode("//", $logo);
        $logo = $logo[1];
	}
}
}

		  if (isset($logo) && !empty($logo)) {
$img = $base_url . "/sites/default/files/styles/medium/public/". $logo;
}
else {
	$img = $base_url . "/sites/default/files/NEC_2022_email_banner_image.png";
}

		  //~ if ($conference_title == "NEC2024") {
//~ $img = $base_url . "/sites/default/files/NEC_2022_email_banner_image2024.png";
//~ }
//~ else {
	//~ $img = $base_url . "/sites/default/files/NEC_2022_email_banner_image.png";
//~ }
	 $params['subject'] = 'Call for Abstract';
	 //$params['message'] = '<img class="" src="'.$base_url.'/sites/default/files/NEC_2022_email_banner_image.png" alt="user img" style="outline:none;text-decoration:none;box-sizing:border-box;max-width:100%;border:none;text-align:center;color:#ff6f6f;font-weight:700;border-radius:5px">';
$params['message'] = ' <img class="" src="'.$img.'" alt="user img" style="outline:none;text-decoration:none;box-sizing:border-box;max-width:100%;border:none;text-align:center;color:#ff6f6f;font-weight:700;border-radius:5px">';

	 $params['message'] .='<p style="font-size: 14px;">Dear '.$first_name .' '.$last_name.',</p>';
	 $params['message'] .='<p style="font-size: 14px;">Thank you for submitting the abstract for the '.$conference_title.'. </p>';
	 //$params['message'] .='<p>The OTP is confidential and for security reasons, DO NOT share the OTP with anyone. </p>';

	$params['message'] .='<table style="font-size: 14px; margin-top:40px; font-size: 1.18rem;"><tr><td><p style="margin:0px; font-size: 14px;"><strong style="color: #15c;">NEC Team</strong></p></td></tr>
	<tr><td style="color: #554848;"><p style="font-size: 14px; margin:0px">Independent Evaluation Office</p></td></tr>
	<tr><td style="color: #554848;"><p style="font-size: 14px; margin:0px">UNDP</p></td></tr>
	</table>';

	$to=$email;

	if (!empty($to)) {
		$result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
		 if ($result['result'] != TRUE) {

			 $message = t('There was a problem sending your email notification to @email.', ['@email' => $to]);
		     \Drupal::messenger()->addMessage($message, 'error');
			 \Drupal::logger('mail-log')->error($message);
			 $tempstore->set('registration_confirm', FALSE);
			 $tempstore->delete('registration_confirm');
		     $tempstore->set('registration_confirm', 2);
			 // return;

    }
	elseif ($result['result'] == TRUE) {
	  $message = t('Call for Abstract Confirmation sent to @email', ['@email' => $to]);
	  \Drupal::messenger()->addMessage($message);
	 \Drupal::logger('mail-log')->notice($message);

	      $tempstore->set('registration_confirm', FALSE);
		  $tempstore->delete('registration_confirm');
		  $tempstore->set('registration_confirm', 1);

	 }

  }



}

  // Show all form values as status message.
 /* foreach ($form_state->getValues() as $key => $value) {
    \Drupal::messenger()->addStatus($key . ': ' . $value);
  }
  * */

  if($stat==0)
  {
  	$path = '/pre_registration/'.$conference.'/verify';
	\Drupal::request()->query->remove('destination');
	$url = Url::fromUserInput($path);
    $form_state->setRedirectUrl($url);
  }
  elseif($stat==1)
  {
  	$path = '/pre_registration/'.$conference.'/final';
	\Drupal::request()->query->remove('destination');
	$url = Url::fromUserInput($path);
    $form_state->setRedirectUrl($url);
  }
 else
	 {

	$path = '/pre_registration_completion/'.$conference;
	\Drupal::request()->query->remove('destination');
	$url = Url::fromUserInput($path);
    $form_state->setRedirectUrl($url);
 }



}

public function getCacheMaxAge() {
    return 0;
  }

}
