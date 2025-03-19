<?php

namespace Drupal\free_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Url;
use Drupal\user\UserStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Entity\User;
use Drupal\Component\Utility\Crypt;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * User registration password controller class.
 */
class PreRegistrationController extends ControllerBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;


 protected $tempStore;
  /**
   * Constructs a UserController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\user\UserStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The status message.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(DateFormatterInterface $date_formatter, UserStorageInterface $user_storage, MessengerInterface $messenger, TimeInterface $time,PrivateTempStoreFactory $temp_store_factory) {
    $this->dateFormatter = $date_formatter;
    $this->userStorage = $user_storage;
    $this->messenger = $messenger;
    $this->time = $time;
    $this->tempStore = $temp_store_factory->get('free_registration');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('messenger'),
      $container->get('datetime.time'),
      $container->get('tempstore.private')
    );
  }

  /**
   * Confirms a user account.
   *
   * @param int $uid
   *   UID of user requesting confirmation.
   * @param int $timestamp
   *   The current timestamp.
   * @param string $hash
   *   Login link hash.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   The form structure or a redirect response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   If the login link is for a blocked user or invalid user ID.
   */


	public function pre_registration($c_id,$email) {
		
		
		
		
	global $base_url;
	  $mailManager = \Drupal::service('plugin.manager.mail');
	  $tempstore = \Drupal::service('tempstore.private')->get('free_registration');
	  $module = 'custom_email';
	  $key = 'join_request';
	  $langcode = \Drupal::currentUser()->getPreferredLangcode();
	  $send = TRUE;
	  $digits = 5;
	  $otp= rand(pow(10, $digits-1), pow(10, $digits)-1);
	  $tempstore->set('otp', $otp);

	  $email = $tempstore->get('email');
//~ $node = \Drupal\node\Entity\Node::load($c_id);
     //~ $title=$node->label();
		  //~ if ($title == "NEC2024") {
//~ $img = $base_url . "/sites/default/files/NEC_2022_email_banner_image2024.png";
//~ }
//~ else {
	//~ $img = $base_url . "/sites/default/files/NEC_2022_email_banner_image.png";
//~ }
$node = \Drupal\node\Entity\Node::load($c_id);
		
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
	  //$config = \Drupal::service('config.factory')->getEditable('free_registration.settings'); // add the cache tag, so that the output gets invalidated when the config is saved
	  //$config->set('otp', $otp)->save();

	  $to = $email;
	  $params['subject'] ='Call for Abstract OTP';
	  //$params['message'] = ' <img class="" src="'.$base_url.'/sites/default/files/NEC_2022_email_banner_image.png" alt="user img" style="outline:none;text-decoration:none;box-sizing:border-box;max-width:100%;border:none;text-align:center;color:#ff6f6f;font-weight:700;border-radius:5px">';
$params['message'] = ' <img class="" src="'.$img.'" alt="user img" style="outline:none;text-decoration:none;box-sizing:border-box;max-width:100%;border:none;text-align:center;color:#ff6f6f;font-weight:700;border-radius:5px">';

	  $params['message'] .='<p style="font-size: 14px;">Dear User,</p>';
	  $params['message'] .='<p style="font-size: 14px;">Your  OTP for Call for Abstract is '. $otp.' </p>';
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

		   return new JsonResponse([
          'stat' =>0,
          'message' =>$message,
          'otp'=>$otp

        ]);
	  }
	  elseif ($result['result'] == TRUE) {
		 $message = t('Sent Invitation');
		 //drupal_set_message($message);
		 \Drupal::logger('mail-log')->notice($message);

		  return new JsonResponse([
          'stat' =>1,
          'message' =>$message,
          'otp'=>$otp

        ]);


	  }


		     return new JsonResponse([
          'stat' =>1,
          'message' =>$message,
          'otp'=>$otp

        ]);

	}



		return new JsonResponse([
          'stat' =>1,
          'message' =>$message,
          'otp'=>$otp

        ]);

}




  public function success($c_id) {

	 $tempstore = \Drupal::service('tempstore.private')->get('free_registration');
     $registration_confirm = $tempstore->get('registration_confirm');
     $node = \Drupal\node\Entity\Node::load($c_id);
     $title=$node->label();
     $path_conference = \Drupal::service('path_alias.manager')->getAliasByPath("/node/".$c_id);

     if($registration_confirm==1)
     {
		 $html="<p> Thank You for Submitting Call for Abstract for <a href='".$path_conference."'>".$title."</a>.<p></p> A Confirmation has been sent to your email address</p>";
		 $build = [
		  '#markup' => $html,
		 ];

	 }
	else if($registration_confirm==2)
     {
		 $html="<p> Thank You for Submitting Call for Abstract for <a href='".$path_conference."'>".$title."</a>. <p></p>There was a problem sending your email notification to email</p>";
		 $build = [
		  '#markup' => $html,
		 ];

	 }
	 else if($registration_confirm==3)
     {
		 $route_options = [];
		 $route_name = '<front>';
		 return $this->redirect($route_name, $route_options);

	 }
	  else if($registration_confirm=='')
     {
		 $route_options = [];
		 $route_name = '<front>';
		 return $this->redirect($route_name, $route_options);

	 }

     $tempstore = \Drupal::service('tempstore.private')->get('free_registration');
     $tempstore->set('registration_confirm', '3');
	 $registration_confirm2 = $tempstore->get('registration_confirm');

	 /*$html="<p> Thank You for Submitting Pre-Registration Abstract for <a href='".$path_conference."'>".$title."</a>. </p><p>There was a problem sending your email notification to email</p>";
		 $build = [
		  '#markup' => $html,
		 ];
	*/

 	\Drupal::service('page_cache_kill_switch')->trigger();
	return $build;
   }

  

}