<?php
/**
 * Themex Lesson
 *
 * Handles lessons data
 *
 * @class ThemexLesson
 * @author Themex
 */
 
class ThemexLesson {

	/** @var array Contains module data. */
	public static $data;

	/**
	 * Adds actions and filters
     *
     * @access public
     * @return void
     */
	public static function init() {
	
		//update lesson
		add_action('wp', array(__CLASS__, 'updateLesson'), 98);
		
		//redirect lesson
		add_action('template_redirect', array(__CLASS__,'filterLesson'), 99);
		
		//update question
		add_action('comment_post', array(__CLASS__, 'updateQuestion'));
		add_filter('preprocess_comment', array(__CLASS__, 'validateQuestion'));
		
		//save attachment
		add_action('template_redirect', array(__CLASS__, 'saveAttachment'), 1);
		
		//add columns
		add_filter('manage_lesson_posts_columns', array(__CLASS__,'addLessonColumns'));
		add_action('manage_lesson_posts_custom_column', array(__CLASS__,'renderLessonColumns'), 10, 2); 
		add_action('pre_get_posts', array(__CLASS__,'filterLessonColumns'));
	}
	
	/**
	 * Refreshes module data
     *
     * @access public	 
     * @return void
     */
	public static function refresh($ID=0, $extended=false) {
		if(!isset(self::$data['ID']) || $ID!=self::$data['ID'] || ($extended && !self::$data['extended'])) {
			self::$data=self::getLesson($ID, $extended);
		}		
	}
	
	/**
	 * Gets lesson
     *
     * @access public
	 * @param int $ID
	 * @param bool $extended
     * @return array
     */
	public static function getLesson($ID, $extended=false) {
		$lesson['ID']=intval($ID);
		$lesson['extended']=$extended;
		$lesson['status']=ThemexCore::getPostMeta($ID, 'lesson_status', 'premium');		
		
		$lesson['progress']=self::getProgress($ID);
		$lesson['course']=ThemexCore::getPostRelations($ID, 0, 'lesson_course', true);
		$lesson['prerequisite']=self::getPrerequisite($ID);
		$lesson['quiz']=self::getQuiz($ID);
		$lesson['attachments']=self::getAttachments($ID);
		$lesson['sidebar']=ThemexCore::getPostMeta($ID, 'lesson_sidebar');
	
		return $lesson;
	}
	
	/**
	 * Updates lesson
     *
     * @access public	 
     * @return void
     */
	public static function updateLesson() {
	
		$data=$_POST;
		if(isset($_POST['data'])) {
			parse_str($_POST['data'], $data);
			$data['nonce']=$_POST['nonce'];
		}

		if(isset($data['lesson_action']) && is_user_logged_in()) {
			self::refresh($data['lesson_id']);
			$redirect=true;
			
			switch(sanitize_key($data['lesson_action'])) {
				case 'complete_lesson':
					self::completeLesson();
				break;
				
				case 'uncomplete_lesson':
					self::uncompleteLesson();
				break;
				
				case 'complete_quiz':
					self::completeQuiz();
				break;
			}
		}
	}
	
	/**
	 * Filters lesson
     *
     * @access public
     * @return void
     */
	public static function filterLesson() {
		global $post;
		$type=get_post_type();
		
		if(in_array($type, array('lesson', 'quiz'))) {
			$lesson=$post->ID;
		
			if($type=='quiz') {
				$lesson=ThemexCore::getPostRelations($post->ID, 0, 'quiz_lesson', true);
			}
			
			$status=ThemexCore::getPostMeta($lesson, 'lesson_status', 'premium');
			$course=ThemexCore::getPostRelations($lesson, 0, 'lesson_course', true);
			
			if($course!=0) {
				ThemexCourse::refresh($course, true);
				$lessons=wp_list_pluck(ThemexCourse::$data['lessons'], 'ID');
				
				if((!ThemexCourse::isSubscriber() || !ThemexCourse::isMember() || !in_array($lesson, $lessons)) && !ThemexCourse::isAuthor() && $status!='free') {
					$redirect=SITE_URL;
					if($course!=0) {
						$redirect=get_permalink($course);
					}
					
					wp_redirect($redirect);
					exit();
				}
			}
		}
	}
	
	/**
	 * Completes lesson
     *
     * @access public
	 * @param int $progress
	 * @param bool $private
     * @return void
     */
	public static function completeLesson($progress=100, $private=false) {
		if((empty(self::$data['quiz']) || $private) && self::$data['progress']==0 && self::$data['prerequisite']['progress']!=0) {
			ThemexCore::addUserRelation(get_current_user_id(), self::$data['ID'], 'lesson', $progress);
		}
	}
	
	/**
	 * Uncompletes lesson
     *
     * @access public
     * @return void
     */
	public static function uncompleteLesson() {
		if(!ThemexCore::checkOption('lesson_retake')) {
			ThemexCore::removeUserRelation(get_current_user_id(), self::$data['ID'], 'lesson');
			
			if(!empty(self::$data['quiz'])) {
				ThemexCore::removeUserRelation(get_current_user_id(), self::$data['quiz']['ID'], 'questions');
				ThemexCore::removeUserRelation(get_current_user_id(), self::$data['quiz']['ID'], 'answers');
			}
		}
	}
	
	/**
	 * Gets lesson progress
     *
     * @access public
	 * @param int $ID
     * @return int
     */
	public static function getProgress($ID) {
		$progress=ThemexCore::getUserRelations(get_current_user_id(), $ID, 'lesson', true);		
		return $progress;
	}
	
	/**
	 * Gets lesson prerequisite
     *
     * @access public
	 * @param int $ID
     * @return array
     */
	public static function getPrerequisite($ID) {
		$prerequisite['ID']=ThemexCore::getPostRelations($ID, 0, 'lesson_lesson', true);		
		$prerequisite['progress']=100;
		
		if($prerequisite['ID']!=0) {
			$prerequisite['progress']=self::getProgress($prerequisite['ID']);
		}
		
		return $prerequisite;
	}
	
	/**
	 * Gets lesson quiz
     *
     * @access public
	 * @param int $ID
     * @return array
     */
	public static function getQuiz($ID) {
		$relation=ThemexCore::getPostRelations(0, $ID, 'quiz_lesson', true);
		$quiz=array();
		
		if($relation!=0) {
			$quiz['ID']=$relation;
			$quiz['percentage']=intval(ThemexCore::getPostMeta($relation, 'quiz_percentage'));
			$quiz['questions']=self::getQuestions($relation);
		}
		
		return $quiz;
	}
	
	/**
	 * Completes lesson quiz
     *
     * @access public
     * @return void
     */
	public static function completeQuiz() {
		if(self::$data['progress']==0) {			
			$answers='';
			$result=0;
			
			if(!empty(self::$data['quiz']['questions'])) {
				foreach(self::$data['quiz']['questions'] as $ID => $question) {
					$correct=false;
					$titles=array();
					
					if(isset($_POST[$ID]) && !empty($question['answers'])) {
						if($question['type']=='string') {
							$answer=reset($question['answers']);
							$titles[]=$_POST[$ID];
							
							if(function_exists('mb_convert_case')) {
								$answer['title']=mb_convert_case($answer['title'], MB_CASE_LOWER, 'UTF-8');
								$_POST[$ID]=mb_convert_case($_POST[$ID], MB_CASE_LOWER, 'UTF-8');
							}
								
							if($_POST[$ID]==$answer['title']) {
								$result++;
								$correct=true;								
							}
						} else if($question['type']=='multiple') {
							$passed=true;
							foreach($question['answers'] as $key => $answer) {
								if(isset($_POST[$ID][$key])) {
									$titles[]=$answer['title'];
								}
								
								if((isset($_POST[$ID][$key]) && !isset($answer['result'])) || (isset($answer['result']) && !isset($_POST[$ID][$key]))) {
									$passed=false;
								}
							}
							
							if($passed) {
								$result++;
								$correct=true;
								$title=$answer['title'];
							}
						} else {
							foreach($question['answers'] as $key => $answer) {
								if($_POST[$ID]==$key) {
									$titles[]=$answer['title'];
									
									if(isset($answer['result'])) {
										$result++;
										$correct=true;									
										
										break;
									}
								}
							}
						}
					}
					
					$titles=array_map('trim', $titles);
					$titles=implode(', ', $titles);
					if(empty($titles)) {
						$titles='–';
					}
					
					$answers.=$question['title'].' , ';
					$answers.=$titles.' , ';
					if($correct) {
						$answers.='correct ; ';
					} else {
						$answers.='incorrect ; ';
					}
				}

				$percentage=round(($result/count(self::$data['quiz']['questions']))*100);
				if(self::$data['quiz']['percentage']==0) {
					$percentage=100;
				}
				
				if($percentage>=self::$data['quiz']['percentage']) {
					if(self::$data['quiz']['percentage']==0) {
						ThemexInterface::$messages[]=__('Congratulations! You have passed this quiz.', 'academy');						
					} else {
						ThemexInterface::$messages[]=sprintf(__('Congratulations! You have passed this quiz achieving %d%%!', 'academy'), $percentage);
					}			
					
					self::completeLesson($percentage, true);
					ThemexCore::addUserRelation(get_current_user_id(), self::$data['quiz']['ID'], 'answers', $answers);
				} else {
					ThemexInterface::$messages[]=sprintf(__('You are required %d%% to pass this quiz.', 'academy'), self::$data['quiz']['percentage']);
				}
			}
		} else {
			wp_redirect(get_permalink(self::$data['ID']));
			exit();
		}
	}
	
	/**
	 * Gets quiz questions
     *
     * @access public
	 * @param int $ID
     * @return array
     */
	public static function getQuestions($ID) {
		$selection=absint(ThemexCore::getPostMeta($ID, 'quiz_selection'));
		$questions=themex_filter(ThemexCore::getPostMeta($ID, 'quiz_questions'));
		
		if(!empty($selection) && $selection<count($questions)) {			
			$keys=ThemexCore::getUserRelations(get_current_user_id(), $ID, 'questions', true);
			if(!empty($keys)) {
				$keys=explode(',', $keys);
				$questions=array_intersect_key($questions, array_flip($keys));
			}
			
			if($selection!=count($questions)) {
				$questions=themex_shuffle($questions);
				$questions=array_slice($questions, 0, $selection);
				$keys=implode(',', array_keys($questions));
				
				ThemexCore::addUserRelation(get_current_user_id(), $ID, 'questions', $keys);
			}
		}
		
		return $questions;
	}
	
	/**
	 * Renders question answers
     *
     * @access public
	 * @param int $ID
	 * @param array $question
     * @return void
     */
	public static function renderAnswers($ID, $question) {		
		$out='<ul>';
		
		if(!empty($question['answers'])) {
			$question['ID']=$ID;
			$visible=false;
			
			if(self::$data['progress']!=0 && ThemexCore::checkOption('lesson_retake')) {
				$visible=true;
			}
			
			if($question['type']=='string') {
				$answer=reset($question['answers']);
				
				$value='';
				if($visible && !self::checkAnswer($question, $answer)) {
					$value=$answer['title'];
				} else if(isset($_POST[$ID])) {
					$value=$_POST[$ID];
				}
				
				$class='';
				if($visible) {
					$class=themex_flag(self::checkAnswer($question, $answer));
				}
				
				$out.='<li class="'.$class.'"><input type="text" name="'.$ID.'" value="'.$value.'" placeholder="'.__('Answer', 'academy').'" /></li>';
			} else {
				if(ThemexCore::checkOption('quiz_shuffle') && count($question['answers'])>2) {
					$question['answers']=themex_shuffle($question['answers']);
				}
				
				if($question['type']=='multiple') {
					foreach($question['answers'] as $key => $answer) {
						$answer['ID']=$key;
						$title=themex_format($answer['title']);
						
						$checked='';
						if(isset($_POST[$ID]) && isset($_POST[$ID][$key])) {
							$checked='checked="checked"';
						}
						
						$class='';
						if($visible) {
							$class=themex_flag(self::checkAnswer($question, $answer));
						}
						
						$out.='<li class="'.$class.'"><input type="checkbox" name="'.$ID.'['.$key.']" id="'.$ID.'_'.$key.'" value="true" '.$checked.' />';
						$out.='<label for="'.$ID.'_'.$key.'">'.$title.'</label></li>';
					}
				} else {
					foreach($question['answers'] as $key => $answer) {
						$answer['ID']=$key;
						$title=themex_format($answer['title']);
						
						$checked='';
						if(isset($_POST[$ID]) && $_POST[$ID]==$key) {
							$checked='checked="checked"';
						}
						
						$class='';
						if($visible) {
							$class=themex_flag(self::checkAnswer($question, $answer));
						}						
						
						$out.='<li class="'.$class.'">';
						$out.='<input type="radio" name="'.$ID.'" id="'.$ID.'_'.$key.'" value="'.$key.'" '.$checked.' />';
						$out.='<label for="'.$ID.'_'.$key.'">'.$title.'</label></li>';
					}
				}
			}
		}
		
		$out.='</ul>';
		
		echo $out;
	}
	
	public static function checkAnswer($question, $answer) {		
		$result=null;
		
		if($question['type']=='string') {
			$result=false;
			
			if(isset($_POST[$question['ID']])) {
				if(function_exists('mb_convert_case')) {
					$answer['title']=mb_convert_case($answer['title'], MB_CASE_LOWER, 'UTF-8');
					$_POST[$question['ID']]=mb_convert_case($_POST[$question['ID']], MB_CASE_LOWER, 'UTF-8');
				}
				
				if($answer['title']==$_POST[$question['ID']]) {
					$result=true;
				}
			}
		} else {		
			if(isset($answer['result'])) {
				$result=true;
			} else if($question['type']=='single' && isset($_POST[$question['ID']]) && $_POST[$question['ID']]==$answer['ID']) {
				$result=false;
			} else if($question['type']=='multiple' && isset($_POST[$question['ID']][$answer['ID']])) {
				$result=false;
			}
		}
		
		return $result;
	}
	
	/**
	 * Updates lesson question
     *
     * @access public
	 * @param int $ID
     * @return void
     */
	public static function updateQuestion($ID) {
		$comment=get_comment($ID);
		$type=get_post_type($comment->comment_post_ID);
		
		if($type=='lesson') {
			add_comment_meta($ID, 'title', sanitize_text_field($_POST['title']));
		}
	}
	
	/**
	 * Validates lesson question
     *
     * @access public
	 * @param array $comment
     * @return array
     */
	public static function validateQuestion($comment) {
		$type=get_post_type($comment['comment_post_ID']);
		
		if($type=='lesson') {
			if($comment['comment_parent']==0 && (!isset($_POST['title']) || empty($_POST['title']))) {
				wp_die('<strong>'.__('ERROR', 'academy').'</strong>: '.__( 'please type a question.', 'academy' ));			
			}
			
			$message=ThemexCore::getOption('email_question');
			if(!empty($comment['comment_parent']) && !empty($message)) {
				$question=get_comment($comment['comment_parent'], ARRAY_A);
				$replies=get_comments(array(
					'parent' => $comment['comment_parent'],					
				));
				
				$emails=wp_list_pluck($replies, 'comment_author_email');
				if(!empty($question)) {
					$emails[]=$question['comment_author_email'];
				}
				
				$emails=array_unique(array_filter($emails));
				foreach($emails as $email) {
					if($email!=$comment['comment_author_email']) {
						$data=get_user_by('email', $email);
					
						if($data!==false) {
							$keywords=array(
								'username' => $data->user_login,
								'title' => get_comment_meta($comment['comment_parent'], 'title', true),
								'link' => get_comment_link($comment['comment_parent']),
							);
							
							themex_mail($email, __('Question Answered', 'academy'), themex_keywords($message, $keywords));
						}
					}					
				}
			}
		}
		
		return $comment;
	}
	
	/**
	 * Renders lesson question
     *
     * @access public
	 * @param mixed $comment
	 * @param array $args
	 * @param int $depth
     * @return void
     */
	public static function renderQuestion($comment, $args, $depth) {
		$GLOBALS['comment']=$comment;
		$GLOBALS['depth']=$depth;
		get_template_part('content', 'question');
	}
	
	public static function hasQuestions($ID) {
		global $wpdb;
		
		$results=$wpdb->get_results("
			SELECT user_id FROM ".$wpdb->comments." 
			WHERE comment_post_ID = ".intval($ID)." 
			AND comment_approved = 1 
			AND comment_type = '' 
			LIMIT 1 
		");
		
		if(empty($results)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Gets lesson attachments
     *
     * @access public
	 * @param int $ID
     * @return array
     */
	public static function getAttachments($ID) {
		$attachments=array_values(themex_filter(ThemexCore::getPostMeta($ID, 'lesson_attachments')));
		
		foreach($attachments as $index => &$attachment) {
			$attachment['redirect']=ThemexCore::getURL('file', themex_encode($index, $ID));		
		}
		
		return $attachments;
	}
	
	/**
	 * Saves lesson attachment
     *
     * @access public
     * @return void
     */
	public static function saveAttachment() {
		$file=ThemexCore::getRewriteRule('file');
		
		if(!empty($file)) {

			$index=themex_decode($file);
			$lesson=themex_decode($file, true);
			
			self::refresh($lesson);
			
			if(!empty(self::$data['course'])) {

				ThemexCourse::refresh(self::$data['course']);
				
				if(
					isset(self::$data['attachments'][$index]) && 
					(
						(
							ThemexCourse::isSubscriber() && 
							ThemexCourse::isMember()) || 
							ThemexCourse::isAuthor() || 
							self::$data['status']=='free'
						)
					) {

					$attachment=self::$data['attachments'][$index];
					
					if(isset($attachment['status']) && $attachment['status']=='link') {

						wp_redirect($attachment['url']);

					} else {

						$name = basename($attachment['url']);
						$file = str_replace(SITE_URL, '', $attachment['url']);

						header('Content-Type: application/octet-stream');
						header('Content-Transfer-Encoding: Binary');
						header('Content-disposition: attachment; filename="'.$name.'"');
						readfile(ABSPATH . $file);

					}
				} else {

					wp_redirect(get_permalink(self::$data['course']));

				}
				
				exit();
			}
			
			wp_redirect(SITE_URL);

			exit();
		}
	}
	
	/**
	 * Adds lesson columns
     *
     * @access public
	 * @param array $columns
     * @return array
     */
	public static function addLessonColumns($columns) {	
		$before=array_slice($columns, 0, 2, true);
		$after=array_slice($columns, 2, count($columns)-2, true);
		$columns['lesson_course']=__('Course', 'academy');
		$columns=array_merge($before, array('lesson_course' => __('Course', 'academy')), $after);
		
		return $columns;
	}
	
	/**
	 * Renders lesson columns
     *
     * @access public
	 * @param string $column
	 * @param int $ID
     * @return void
     */
	public static function renderLessonColumns($column, $ID) {
		if($column=='lesson_course'){
			$ID=ThemexCore::getPostRelations($ID, 0, 'lesson_course', true);
			$title='&mdash;';
			
			if($ID!=0) {
				$title='<a href="'.admin_url('edit.php').'?post_type=lesson&lesson_course='.$ID.'">'.get_the_title($ID).'</a>';
			}
			
			echo $title;
		}
	}
	
	/**
	 * Filters lesson columns
     *
     * @access public
	 * @param mixed $query
     * @return mixed
     */
	public static function filterLessonColumns($query) {
		global $pagenow;
		
		if(is_admin() && $pagenow=='edit.php' && isset($_GET['lesson_course'])) {
			$lessons=ThemexCore::getPostRelations(0, intval($_GET['lesson_course']), 'lesson_course');			
			if(!empty($lessons)) {
				$query->set('post__in', $lessons);
			}
		}
		
		return $query;
	}
}