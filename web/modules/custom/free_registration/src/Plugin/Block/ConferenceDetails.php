<?php

namespace Drupal\free_registration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
/**
 * Provides a 'ConferenceDetails' block.
 *
 * @Block(
 *  id = "ConferenceDetails",
 *  admin_label = @Translation("Conference Details in Pre-Registration Abstract"),
 * )
 */
class ConferenceDetails extends BlockBase {
  public function build() {
    $build = [];
    $current_path = \Drupal::service('path.current')->getPath();
     $current_path = \Drupal::service('path.current')->getPath();
     $path = explode("/",$current_path);
     $nid = $path[2];

		  if(is_numeric($nid))
		  {

			  $conf_det = \Drupal\node\Entity\Node::load($nid);
			  $path = \Drupal::service('path_alias.manager')->getAliasByPath("/node/".$nid);
			  $title=$conf_det->label();
			  $field_about_description = $conf_det->get('body')->getValue()[0]['value'];
			  $trimmed_text = substr($field_about_description, 0, 600) . '... <a href="'.$path.'">Read More</a>';

			  /*$query= db_query("SELECT taxonomy_term_field_data.tid AS tid  FROM {taxonomy_term_field_data} taxonomy_term_field_data
LEFT JOIN {taxonomy_term__field_conference} taxonomy_term__field_conference ON taxonomy_term_field_data.tid = taxonomy_term__field_conference.entity_id AND taxonomy_term__field_conference.deleted = '0' AND (taxonomy_term__field_conference.langcode = taxonomy_term_field_data.langcode OR taxonomy_term__field_conference.bundle = 'hotels')
WHERE ((taxonomy_term__field_conference.field_conference_target_id = :confid)) AND ((taxonomy_term_field_data.status = '1') AND (taxonomy_term_field_data.vid IN ('bursary_application')))",array(':confid' => $nid));*/

				$query = \Drupal::database()->select('taxonomy_term_field_data', 't');
				$query->fields('t',['tid']);
				$query->leftJoin('taxonomy_term__field_conference', 'c','c.entity_id = t.tid AND c.deleted = 0 AND (c.langcode = t.langcode)');
				$query->condition('c.field_conference_target_id',$nid,'=');
				$query->condition('t.status',1,'=');
				$query->condition('t.vid',['bursary_application'],'IN');
				$result = $query->execute();
	  		$records = $result->fetchAll();

									foreach($records as $res){
										$i=1;
										$taxonomy_term_id = $res->tid;
										$term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($taxonomy_term_id);
										$tem_name = $term->name->value;
										$body = $term->description->value;
										$field_conclusion = $term->field_conclusion->value;
										if($term->field_abstract_template->entity)
										{
											$field_abstract_template=\Drupal::service('file_url_generator')->generateAbsoluteString($term->field_abstract_template->entity->getFileUri());
										}
										if($term->field_bursary_support_applicatio->entity)
										{
											$field_bursary_support_applicatio=\Drupal::service('file_url_generator')->generateAbsoluteString($term->field_bursary_support_applicatio->entity->getFileUri());
										}
										if($term->field_bursary_programme_informat->entity)
										{
											$field_bursary_programme_informat=\Drupal::service('file_url_generator')->generateAbsoluteString($term->field_bursary_programme_informat->entity->getFileUri());
										}



									}

			 // <p class="conf_abstract-details">'.$trimmed_text.'</p>
			 //<p>Download and complete the Abstract Template </p>
			//					<button type="button" class="btn btn-primary btn-download"><a href="'.$field_abstract_template.'" download>Download</a></button>

				 $build['#markup'] = '
				 <div class="abstract_header">
				 <h1 class="page-header"> Call for Abstract - '.$title.'</h1>
				   </div>
				 <div class="abstract_wrapper">
				 <div class="form_custom_title">Upload Abstract </div>
				 <div class="conf_abstract-details">
				 <div class="instructions">Instructions to upload</div>
						<ol class"instruction_list">
						<li><strong>Step 1</strong>: Enter your name and email and click on ‘Submit’ button. This will enable One Time Password (OTP) and file upload fields.</li>
						<li><strong>Step 2</strong>: The system will send an OTP to your registered email. Enter this code in the OTP field. (Please check your Spam or Junk folder, incase you did not receive the OTP in your Inbox).</li>
						<li><strong>Step 3</strong>: Once you enter the OTP, you will be able to upload your abstract. Abstract should be "max 500 words" follow the  <span class="dwnload" style="text-decoration: underline!important;"><a style="text-decoration: underline!important;" href="'.$field_abstract_template.'" download>abstract template</a></span> provided for '.$title.' conference.</li>
					</ol>
					<div class="abstract_downoad conference-steps"><p>Download the Abstract Template </p>
							<button type="button" class="btn btn-primary btn-download"><a href="'.$field_abstract_template.'" download>Download</a></button></div>

					<div class="conclusion">Thank you for your interest in '.$title.'.</div>
				 </div>
				 </div>







';

			 }
			 else
			 {
				 $build['#markup'] = '';
			 }


    /* enabled or not */



    return $build;
  }
  public function getCacheMaxAge() {
    return 0;
  }
}
