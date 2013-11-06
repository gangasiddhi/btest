{*<script type="text/javascript" src="{$content_dir}js/conditions.js"></script>*}
<div id="second-header">
	<h2>{l s='Style Survey'}</h2>
</div>


{if $step_count == 1}

{*include file="$tpl_dir./errors.tpl"*}

<div class="style_survey">

	{* <div style="margin: 0pt; padding: 0pt; display: inline;">
			<input type="hidden" value="" name="authenticity_token" id="authenticity_token"/>
		</div> *}

	<div id="quiz">

		<div class="inner">

            <div class="panelContainer" style="visibility:hidden;">
			<form id="quiz_form" name="quiz_form" method="post" action="{$link->getPageLink('stylesurvey.php')}?stp={$step_count}&cemail={$cemail}{if isset($fb_cust_name)}&fb_cust_name={$fb_cust_name}{/if}" style="margin: 0pt; padding: 0pt;">

				<div class="panel">
                    <div id="question_text_1000" class="question">{l s='Question 1'}{if isset($cemail)}<a href="{$link->getPageLink('lookbook.php')}?stp=1{if isset($cemail)}&cemail={$cemail}{/if}" id="skip_survey_link">{l s='Skip Survey'}</a>{/if}</div>
					<input type="hidden" value="" id="q_1000" name="qqa_1000"/>

					<div class="choices celeb">
						<div class="name">
							<a onclick="$('#q_1000').val(1); scroll_to_pane(2); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 1A'}" src="{$img_ps_dir}survey/q1/1.jpg"/>
							</a>
						</div>
						<div class="name gap">
							<a onclick="$('#q_1000').val(2); scroll_to_pane(2); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 1B'}" src="{$img_ps_dir}survey/q1/2.jpg"/>
							</a>
						</div>
						<div class="name">
							<a onclick="$('#q_1000').val(3); scroll_to_pane(2); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 1C'}" src="{$img_ps_dir}survey/q1/3.jpg"/>
							</a>
						</div>
					</div>
				</div>

				<div class="panel">
					<div id="question_text_1001" class="question">{l s='Question 2'}{if isset($cemail)}<a href="{$link->getPageLink('lookbook.php')}?stp=1{if isset($cemail)}&cemail={$cemail}{/if}" id="skip_survey_link">{l s='Skip Survey'}</a>{/if}</div>
					<input type="hidden" value="" id="q_1001" name="qqa_1001"/>

					<div class="choices celeb">
						<div class="name">
							<a onclick="$('#q_1001').val(1); scroll_to_pane(3); return false;" href="">
								<img height="315" width="210" alt="{l s='Answer 2A'}" src="{$img_ps_dir}survey/q2/1.jpg"/>
							</a>
							{l s='Answer 2A'}
						</div>
						<div class="name gap">
							<a onclick="$('#q_1001').val(2); scroll_to_pane(3); return false;" href="">
								<img height="315" width="210" alt="{l s='Answer 2B'}" src="{$img_ps_dir}survey/q2/2.jpg"/>
							</a>
							{l s='Answer 2B'}
						</div>
						<div class="name">
							<a onclick="$('#q_1001').val(3); scroll_to_pane(3); return false;" href="">
								<img height="315" width="210" alt="{l s='Answer 2C'}" src="{$img_ps_dir}survey/q2/3.jpg"/>
							</a>
							{l s='Answer 2C'}
						</div>
					</div>
				</div>

				<div class="panel">
					<div id="question_text_1002" class="question">{l s='Question 3'}{if isset($cemail)}<a href="{$link->getPageLink('lookbook.php')}?stp=1{if isset($cemail)}&cemail={$cemail}{/if}" id="skip_survey_link">{l s='Skip Survey'}</a>{/if}</div>
					<input type="hidden" value="" id="q_1002" name="qqa_1002"/>

					<div class="choices celeb">
						<div class="name">
							<a onclick="$('#q_1002').val(1); scroll_to_pane(4); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 3A'}" src="{$img_ps_dir}survey/q3/1.jpg"/>
							</a>
						</div>
						<div class="name gap">
							<a onclick="$('#q_1002').val(2); scroll_to_pane(4); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 3B'}" src="{$img_ps_dir}survey/q3/2.jpg"/>
							</a>
						</div>
						<div class="name">
							<a onclick="$('#q_1002').val(3); scroll_to_pane(4); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 3C'}" src="{$img_ps_dir}survey/q3/3.jpg"/>
							</a>
						</div>
					</div>
				</div>

				<div class="panel">
					<div id="question_text_1003" class="question">{l s='Question 4'}{if isset($cemail)}<a href="{$link->getPageLink('lookbook.php')}?stp=1{if isset($cemail)}&cemail={$cemail}{/if}" id="skip_survey_link">{l s='Skip Survey'}</a>{/if}</div>
					<input type="hidden" value="" id="q_1003" name="qqa_1003"/>

					<div class="choices celeb">
						<div class="name">
							<a onclick="$('#q_1003').val(1); scroll_to_pane(5); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 4A'}" src="{$img_ps_dir}survey/q4/1.jpg"/>
							</a>
						</div>
						<div class="name gap">
							<a onclick="$('#q_1003').val(2); scroll_to_pane(5); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 4B'}" src="{$img_ps_dir}survey/q4/2.jpg"/>
							</a>
						</div>
						<div class="name">
							<a onclick="$('#q_1003').val(3); scroll_to_pane(5); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 4C'}" src="{$img_ps_dir}survey/q4/3.jpg"/>
							</a>
						</div>
					</div>
				</div>

				<div class="panel">
					<div id="question_text_1004" class="question">{l s='Question 5'}{if isset($cemail)}<a href="{$link->getPageLink('lookbook.php')}?stp=1{if isset($cemail)}&cemail={$cemail}{/if}" id="skip_survey_link">{l s='Skip Survey'}</a>{/if}</div>
					<input type="hidden" value="" id="q_1004" name="qqa_1004"/>

					<div class="choices celeb">
						<div class="name">
							<a onclick="$('#q_1004').val(1); scroll_to_pane(6); return false;" href="">
								<img height="315" width="210" alt="{l s='Answer 5A'}" src="{$img_ps_dir}survey/q5/1.jpg"/>
							</a>
							{l s='Answer 5A'}
						</div>
						<div class="name gap">
							<a onclick="$('#q_1004').val(2); scroll_to_pane(6); return false;" href="">
								<img height="315" width="210" alt="{l s='Answer 5B'}" src="{$img_ps_dir}survey/q5/2.jpg"/>
							</a>
							{l s='Answer 5B'}
						</div>
						<div class="name">
							<a onclick="$('#q_1004').val(3); scroll_to_pane(6); return false;" href="">
								<img height="315" width="210" alt="{l s='Answer 5C'}" src="{$img_ps_dir}survey/q5/3.jpg"/>
							</a>
							{l s='Answer 5C'}
						</div>
					</div>
				</div>

				<div class="panel">
					<div id="question_text_1005" class="question">{l s='Question 6'}{if isset($cemail)}<a href="{$link->getPageLink('lookbook.php')}?stp=1{if isset($cemail)}&cemail={$cemail}{/if}" id="skip_survey_link">{l s='Skip Survey'}</a>{/if}</div>
					<input type="hidden" value="" id="q_1005" name="qqa_1005"/>

					<div class="choices celeb">
						<div class="name">
							<a onclick="$('#q_1005').val(1); scroll_to_pane(7); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 6A'}" src="{$img_ps_dir}survey/q6/1.jpg"/>
							</a>
						</div>
						<div class="name gap">
							<a onclick="$('#q_1005').val(2); scroll_to_pane(7); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 6B'}" src="{$img_ps_dir}survey/q6/2.jpg"/>
							</a>
						</div>
						<div class="name">
							<a onclick="$('#q_1005').val(3); scroll_to_pane(7); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 6C'}" src="{$img_ps_dir}survey/q6/3.jpg"/>
							</a>
						</div>
					</div>
				</div>

				<div class="panel">
					<div id="question_text_1006" class="question">{l s='Question 7'}{if isset($cemail)}<a href="{$link->getPageLink('lookbook.php')}?stp=1{if isset($cemail)}&cemail={$cemail}{/if}" id="skip_survey_link">{l s='Skip Survey'}</a>{/if}</div>
					<input type="hidden" value="" id="q_1006" name="qqa_1006"/>

					<div class="choices celeb">
						<div class="name">
							<a onclick="$('#q_1006').val(1); scroll_to_pane(8); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 7A'}" src="{$img_ps_dir}survey/q7/1.jpg"/>
							</a>
						</div>
						<div class="name gap">
							<a onclick="$('#q_1006').val(2); scroll_to_pane(8); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 7B'}" src="{$img_ps_dir}survey/q7/2.jpg"/>
							</a>
						</div>
						<div class="name">
							<a onclick="$('#q_1006').val(3); scroll_to_pane(8); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 7C'}" src="{$img_ps_dir}survey/q7/3.jpg"/>
							</a>
						</div>
					</div>
				</div>

				<div class="panel">
					<div id="question_text_1007" class="question">{l s='Question 8'}{if isset($cemail)}<a href="{$link->getPageLink('lookbook.php')}?stp=1{if isset($cemail)}&cemail={$cemail}{/if}" id="skip_survey_link">{l s='Skip Survey'}</a>{/if}</div>
					<input type="hidden" value="" id="q_1007" name="qqa_1007"/>

					<div class="choices celeb">
						<div class="name">
							<a onclick="$('#q_1007').val(1); scroll_to_pane(9); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 8A'}" src="{$img_ps_dir}survey/q8/1.jpg"/>
							</a>
						</div>
						<div class="name gap">
							<a onclick="$('#q_1007').val(2); scroll_to_pane(9); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 8B'}" src="{$img_ps_dir}survey/q8/2.jpg"/>
							</a>
						</div>
						<div class="name">
							<a onclick="$('#q_1007').val(3); scroll_to_pane(9); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 8C'}" src="{$img_ps_dir}survey/q8/3.jpg"/>
							</a>
						</div>
					</div>
				</div>

				<div class="panel">
					<div id="question_text_1008" class="question">{l s='Question 9'}{if isset($cemail)}<a href="{$link->getPageLink('lookbook.php')}?stp=1{if isset($cemail)}&cemail={$cemail}{/if}" id="skip_survey_link">{l s='Skip Survey'}</a>{/if}</div>
					<input type="hidden" value="" id="q_1008" name="qqa_1008"/>

					<div class="choices celeb">
						<div class="name">
							<a onclick="$('#q_1008').val(1); scroll_to_pane(10); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 9A'}" src="{$img_ps_dir}survey/q9/1.jpg"/>
							</a>
						</div>
						<div class="name gap">
							<a onclick="$('#q_1008').val(2); scroll_to_pane(10); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 9B'}" src="{$img_ps_dir}survey/q9/2.jpg"/>
							</a>
						</div>
						<div class="name">
							<a onclick="$('#q_1008').val(3); scroll_to_pane(10); return false;" href="">
								<img height="210" width="210" alt="{l s='Answer 9C'}" src="{$img_ps_dir}survey/q9/3.jpg"/>
							</a>
						</div>
					</div>
				</div>

				<div class="panel">
					<div id="question_text_1009" class="question">{l s='Which of the following colors do you like the most?'}{if isset($cemail)}<a href="{$link->getPageLink('lookbook.php')}?stp=1{if isset($cemail)}&cemail={$cemail}{/if}" id="skip_survey_link_1">{l s='Skip Survey'}</a>{/if}</div>
					<input type="hidden" value="" id="q_1009_1" name="qqa_1009_1"/>
					<input type="hidden" value="" id="q_1009_2" name="qqa_1009_2"/>
					<input type="hidden" value="" id="q_1009_3" name="qqa_1009_3"/>
					<input type="hidden" value="" id="q_1009_4" name="qqa_1009_4"/>
					<input type="hidden" value="" id="q_1009_5" name="qqa_1009_5"/>
					<input type="hidden" value="" id="q_1009_6" name="qqa_1009_6"/>
					<input type="hidden" value="" id="q_1009_7" name="qqa_1009_7"/>
					<input type="hidden" value="" id="q_1009_8" name="qqa_1009_8"/>
					<input type="hidden" value="" id="q_1009_9" name="qqa_1009_9"/>
					<input type="hidden" value="" id="q_1009_10" name="qqa_1009_10"/>
					<input type="hidden" value="" id="q_1009_11" name="qqa_1009_11"/>
					<input type="hidden" value="" id="q_1009_12" name="qqa_1009_12"/>

					<div class="continue_survey">
						<span style="color:red; font-weight:bold; margin:0 10px; display:none" id="missing_answer_10">{l s='Please answer the required questions.'}</span>
						<a href="" title="{l s='Continue'}" style="display: block" class="buttons button_continue" onclick="check_page(10, 12); return false;">
							<span class = "buttonmedium blue" style="float: left;">{l s='Continue'}</span>
						</a>
					</div>

					<div class="choices color">
						<div class="color_button" id="q10_1" onclick="toggleMultiChoiceSurveySelect(10,1,1,1009,12); return false;" >
							<div class="color_image">
								<img src ="{$img_ps_dir}survey/colors/beige.gif" alt="{l s='Beige'}"/>
							</div>
							<span class="color_name">{l s='Beige'}</span>
						</div>
						<div class="color_button" id="q10_2" onclick="toggleMultiChoiceSurveySelect(10,2,1,1009,12); return false;" >
							<div class="color_image">
								<img src ="{$img_ps_dir}survey/colors/black.gif" alt="{l s='Black'}"/>
							</div>
							<span class="color_name">{l s='Black'}</span>
						</div>
						<div class="color_button" id="q10_3" onclick="toggleMultiChoiceSurveySelect(10,3,1,1009,12); return false;" >
							<div class="color_image">
								<img src ="{$img_ps_dir}survey/colors/browns.gif" alt="{l s='Browns'}"/>
							</div>
							<span class="color_name">{l s='Browns'}</span>
						</div>
						<div class="color_button" id="q10_4" onclick="toggleMultiChoiceSurveySelect(10,4,1,1009,12); return false;" >
							<div class="color_image">
								<img src ="{$img_ps_dir}survey/colors/blue.gif" alt="{l s='Blues'}"/>
							</div>
							<span class="color_name">{l s='Blues'}</span>
						</div>
						<div class="color_button" id="q10_5" onclick="toggleMultiChoiceSurveySelect(10,5,1,1009,12); return false;" >
							<div class="color_image">
								<img src ="{$img_ps_dir}survey/colors/grays.gif" alt="{l s='Grays'}"/>
							</div>
							<span class="color_name">{l s='Grays'}</span>
						</div>
						<div class="color_button" id="q10_6" onclick="toggleMultiChoiceSurveySelect(10,6,1,1009,12); return false;" >
							<div class="color_image">
								<img src ="{$img_ps_dir}survey/colors/greens.gif" alt="{l s='Greens'}"/>
							</div>
							<span class="color_name">{l s='Greens'}</span>
						</div>
						<div class="color_button"id="q10_7" onclick="toggleMultiChoiceSurveySelect(10,7,1,1009,12); return false;" >
							<div class="color_image">
								<img src ="{$img_ps_dir}survey/colors/orange.gif" alt="{l s='Orange'}"/>
							</div>
							<span class="color_name">{l s='Orange'}</span>
						</div>
						<div class="color_button" id="q10_8" onclick="toggleMultiChoiceSurveySelect(10,8,1,1009,12); return false;" >
							<div class="color_image">
								<img src ="{$img_ps_dir}survey/colors/pink.gif" alt="{l s='Pinks'}"/>
							</div>
							<span class="color_name">{l s='Pinks'}</span>
						</div>
						<div class="color_button" id="q10_9" onclick="toggleMultiChoiceSurveySelect(10,9,1,1009,12); return false;" >
							<div class="color_image">
								<img src ="{$img_ps_dir}survey/colors/purple.gif" alt="{l s='Purple'}"/>
							</div>
							<span class="color_name">{l s='Purple'}</span>
						</div>
						<div class="color_button" id="q10_10" onclick="toggleMultiChoiceSurveySelect(10,10,1,1009,12); return false;" >
							<div class="color_image">
								<img src ="{$img_ps_dir}survey/colors/reds.gif" alt="{l s='Reds'}"/>
							</div>
							<span class="color_name">{l s='Reds'}</span>
						</div>
						<div class="color_button" id="q10_11" onclick="toggleMultiChoiceSurveySelect(10,11,1,1009,12); return false;" >
							<div class="color_image">
								<img src ="{$img_ps_dir}survey/colors/yellows.gif" alt="{l s='Yellows'}"/>
							</div>
							<span class="color_name">{l s='Yellows'}</span>
						</div>
						<div class="color_button" id="q10_12" onclick="toggleMultiChoiceSurveySelect(10,12,1,1009,12); return false;" >
							<div class="color_image">
								<img src ="{$img_ps_dir}survey/colors/white.gif" alt="{l s='White'}"/>
							</div>
							<span class="color_name">{l s='White'}</span>
						</div>
					</div>
				</div>

				{*<div class="panel">
					<div id="question_text_1009" class="question">{l s='Which shoe styles do you like the most?'}</div>
					<input type="hidden" value="" id="q_1009_1" name="qqa_1009_1"/>
					<input type="hidden" value="" id="q_1009_2" name="qqa_1009_2"/>
					<input type="hidden" value="" id="q_1009_3" name="qqa_1009_3"/>
					<input type="hidden" value="" id="q_1009_4" name="qqa_1009_4"/>
					<input type="hidden" value="" id="q_1009_5" name="qqa_1009_5"/>
					<input type="hidden" value="" id="q_1009_6" name="qqa_1009_6"/>
					<input type="hidden" value="" id="q_1009_7" name="qqa_1009_7"/>
					<input type="hidden" value="" id="q_1009_8" name="qqa_1009_8"/>
					<input type="hidden" value="" id="q_1009_9" name="qqa_1009_9"/>
					<input type="hidden" value="" id="q_1009_10" name="qqa_1009_10"/>
					<input type="hidden" value="" id="q_1009_11" name="qqa_1009_11"/>
					<input type="hidden" value="" id="q_1009_12" name="qqa_1009_12"/>
					<input type="hidden" value="" id="q_1009_13" name="qqa_1009_13"/>
					<input type="hidden" value="" id="q_1009_14" name="qqa_1009_14"/>
					<input type="hidden" value="" id="q_1009_15" name="qqa_1009_15"/>
					<input type="hidden" value="" id="q_1009_16" name="qqa_1009_16"/>

					<div class="continue_survey">
						<span style="color:red; font-weight:bold; margin:0 10px; display:none" id="missing_answer_10">{l s='Please answer the required questions.'}</span>
						<a href="" title="{l s='Continue'}" class="buttons button_continue" onclick="check_page(10, 16); return false;">
							<span>{l s='Continue'}</span>
						</a>
					</div>

					<div class="choices shoe">
						<div class="shoe_button" id="q10_1" onclick="toggleMultiChoiceSurveySelect(10,1,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/shortboot.gif" alt="{l s='Short Boot'}"/></span>
							<span class="shoe_name">{l s='Short Boot'}</span>
						</div>
						<div class="shoe_button" id="q10_2" onclick="toggleMultiChoiceSurveySelect(10,2,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/flat.gif" alt="{l s='Flat'}"/></span>
							<span class="shoe_name">{l s='Flat'}</span>
						</div>
						<div class="shoe_button" id="q10_3" onclick="toggleMultiChoiceSurveySelect(10,3,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/highheel.gif" alt="{l s='High Heel'}"/></span>
							<span class="shoe_name">{l s='High Heel'}</span>
						</div>
						<div class="shoe_button" id="q10_4" onclick="toggleMultiChoiceSurveySelect(10,4,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/wedge.gif" alt="{l s='Wedge'}"/></span>
							<span class="shoe_name">{l s='Wedge'}</span>
						</div>
						<div class="shoe_button" id="q10_5" onclick="toggleMultiChoiceSurveySelect(10,5,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/lowheel.gif" alt="{l s='Low Heel'}"/></span>
							<span class="shoe_name">{l s='Low Heel'}</span>
						</div>
						<div class="shoe_button" id="q10_6" onclick="toggleMultiChoiceSurveySelect(10,6,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/midheel.gif" alt="{l s='Mid Heel'}"/></span>
							<span class="shoe_name">{l s='Mid Heel'}</span>
						</div>
						<div class="shoe_button" id="q10_7" onclick="toggleMultiChoiceSurveySelect(10,7,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/tallboot.gif" alt="{l s='Tall Boot'}"/></span>
							<span class="shoe_name">{l s='Tall Boot'}</span>
						</div>
						<div class="shoe_button" id="q10_8" onclick="toggleMultiChoiceSurveySelect(10,8,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/pointytoe.gif" alt="{l s='Pointy Toe'}"/></span>
							<span class="shoe_name">{l s='Pointy Toe'}</span>
						</div>
						<div class="shoe_button" id="q10_9" onclick="toggleMultiChoiceSurveySelect(10,9,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/opentoe.gif" alt="{l s='Open Toe'}"/></span>
							<span class="shoe_name">{l s='Open Toe'}</span>
						</div>
						<div class="shoe_button" id="q10_10" onclick="toggleMultiChoiceSurveySelect(10,10,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/roundtoe.gif" alt="{l s='Round Toe'}"/></span>
							<span class="shoe_name">{l s='Round Toe'}</span>
						</div>
						<div class="shoe_button" id="q10_11" onclick="toggleMultiChoiceSurveySelect(10,11,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/squaretoe.gif" alt="{l s='Square Toe'}"/></span>
							<span class="shoe_name">{l s='Square Toe'}</span>
						</div>
						<div class="shoe_button" id="q10_12" onclick="toggleMultiChoiceSurveySelect(10,12,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/strappy.gif" alt="{l s='Strappy'}"/></span>
							<span class="shoe_name">{l s='Strappy'}</span>
						</div>
						<div class="shoe_button" id="q10_13" onclick="toggleMultiChoiceSurveySelect(10,13,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/thong.gif" alt="{l s='Thong'}"/></span>
							<span class="shoe_name">{l s='Thong'}</span>
						</div>
						<div class="shoe_button" id="q10_14" onclick="toggleMultiChoiceSurveySelect(10,14,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/maryjane.gif" alt="{l s='Mary Jane'}"/></span>
							<span class="shoe_name">{l s='Mary Jane'}</span>
						</div>
						<div class="shoe_button" id="q10_15" onclick="toggleMultiChoiceSurveySelect(10,15,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/slingback.gif" alt="{l s='Slingback'}"/></span>
							<span class="shoe_name">{l s='Slingback'}</span>
						</div>
						<div class="shoe_button" id="q10_16" onclick="toggleMultiChoiceSurveySelect(10,16,1,1009,16); return false;">
							<span class="shoe_image"><img src="{$img_ps_dir}survey/shoes/anklestrap.jpg" alt="{l s='Ankle Strap'}"/></span>
							<span class="shoe_name">{l s='Ankle Strap'}</span>
						</div>
					</div>

				</div>*}

				<div class="panel">
					<div id="question_text_1010" class="question">{l s='Please select your age range, shoe size and dress size.'}{if isset($cemail)}<a href="{$link->getPageLink('lookbook.php')}?stp=1{if isset($cemail)}&cemail={$cemail}{/if}" id="skip_survey_link_1">{l s='Skip Survey'}</a>{/if}</div>
					<input type="hidden" value="" id="q_1010" name="qqa_1010"/>
					<input type="hidden" value="" id="q_1011" name="qqa_1011"/>
					<input type="hidden" value="" id="q_1012" name="qqa_1012"/>

					<div class="continue_survey">
						<span style="color:red; font-weight:bold; margin:0 10px; display:none" id="missing_answer_11">{l s='Please answer the required questions.'}</span>
						<a href="" title="{l s='Continue'}" style="display: block" class="buttons button_continue" onclick="check_page(11, 0); return false;">
							<span class = "buttonmedium blue" style="float: left;">{l s='Continue'}</span>
						</a>
					</div>

					<div class="choices">

						<div class="ques-row">
							<div class="ques-left">
								<span id="question_text_1010_1">{l s='My age range'}</span>
								<img src="{$img_ps_dir}survey/cake.gif" alt="{l s='My age range'}"/>
							</div>
							<div class="multiCol ageCol clearAfter">
								<div class="col first" id="q10_1">
									<a id="qsp_1" onclick="toggleAgeChoiceSurvey(1,1,1010,5); return false;" href="">{l s='18-23'}</a>
								</div>
								<div class="col " id="q10_2">
									<a id="qsp_2" onclick="toggleAgeChoiceSurvey(2,2,1010,5); return false;" href="">{l s='24-29'}</a>
								</div>
								<div class="col " id="q10_3">
									<a id="qsp_3" onclick="toggleAgeChoiceSurvey(3,3,1010,5); return false;" href="">{l s='30-35'}</a>
								</div>
								<div class="col " id="q10_4">
									<a id="qsp_4" onclick="toggleAgeChoiceSurvey(4,4,1010,5); return false;" href="">{l s='36-45'}</a>
								</div>
								<div class="col " id="q10_5">
									<a id="qsp_5" onclick="toggleAgeChoiceSurvey(5,5,1010,5); return false;" href="">{l s='46+'}</a>
								</div>
							</div>
						</div>

						<div class="ques-row ques-row-auto-width ques-row-auto-height">
							<div class="ques-left">
								<span id="question_text_1010_2">{l s='My shoe size'}</span>
								<img src="{$img_ps_dir}survey/shoe.gif" alt="{l s='My shoe size'}"/>
							</div>
							<div class="multiCol shoeCol clearAfter">
								<div class="col first" id="q11_1">
									<a onclick="toggleSingleChoiceSurvey(11,1,1,1011,8); return false;" href="">{l s='35'}</a>
								</div>
								<div class="col " id="q11_2">
									<a onclick="toggleSingleChoiceSurvey(11,2,2,1011,8); return false;" href="">{l s='36'}</a>
								</div>
								<div class="col " id="q11_3">
									<a onclick="toggleSingleChoiceSurvey(11,3,3,1011,8); return false;" href="">{l s='37'}</a>
								</div>
								<div class="col " id="q11_4">
									<a onclick="toggleSingleChoiceSurvey(11,4,4,1011,8); return false;" href="">{l s='38'}</a>
								</div>
								<div class="col " id="q11_5">
									<a onclick="toggleSingleChoiceSurvey(11,5,5,1011,8); return false;" href="">{l s='39'}</a>
								</div>
								<div class="col " id="q11_6">
									<a onclick="toggleSingleChoiceSurvey(11,6,6,1011,8); return false;" href="">{l s='40'}</a>
								</div>
								<div class="col " id="q11_7">
									<a onclick="toggleSingleChoiceSurvey(11,7,7,1011,8); return false;" href="">{l s='41'}</a>
								</div>
								<div class="col " id="q11_8">
									<a onclick="toggleSingleChoiceSurvey(11,8,8,1011,8); return false;" href="">{l s='42'}</a>
								</div>
							</div>
						</div>

						<div class="ques-row">
							<div class="ques-left">
								<span id="question_text_1007_3">{l s='My dress size'}</span>
								<img src="{$img_ps_dir}survey/dress.gif" alt="{l s='My dress size'}"/>
							</div>
							<div class="multiCol dressCol clearAfter">
								<div class="col first" id="q12_1">
									<a onclick="toggleSingleChoiceSurvey(12,1,1,1012,5); return false;" href="">{l s='0-4'}</a>
								</div>
								<div class="col" id="q12_2">
									<a onclick="toggleSingleChoiceSurvey(12,2,2,1012,5); return false;" href="">{l s='6-8'}</a>
								</div>
								<div class="col" id="q12_3">
									<a onclick="toggleSingleChoiceSurvey(12,3,3,1012,5); return false;" href="">{l s='10-12'}</a>
								</div>
								<div class="col" id="q12_4">
									<a onclick="toggleSingleChoiceSurvey(12,4,4,1012,5); return false;" href="">{l s='14-16'}</a>
								</div>
								<div class="col" id="q12_5">
									<a onclick="toggleSingleChoiceSurvey(12,5,5,1012,5); return false;" href="">{l s='18+'}</a>
								</div>
							</div>
						</div>

					</div>

					{*<input type="hidden" name="ref_by" value="{if isset($smarty.post.ref_by)}{$smarty.post.ref_by|escape:'htmlall':'UTF-8'}{/if}" />*}
				</div>

			</form>
			</div>

		</div>{* end inner div *}

		<br class="clear"/>

		<div class="status">
			<div style="" id="status_1" class="bar percent0">0%</div>
			<div style="display: none;" id="status_2" class="bar percent9">9%</div>
			<div style="display: none;" id="status_3" class="bar percent19">19%</div>
			<div style="display: none;" id="status_4" class="bar percent28">28%</div>
			<div style="display: none;" id="status_5" class="bar percent38">38%</div>
			<div style="display: none;" id="status_6" class="bar percent47">47%</div>
			<div style="display: none;" id="status_7" class="bar percent57">57%</div>
			<div style="display: none;" id="status_8" class="bar percent66">66%</div>
			<div style="display: none;" id="status_9" class="bar percent76">76%</div>
			<div style="display: none;" id="status_10" class="bar percent85">85%</div>
			<div style="display: none;" id="status_11" class="bar percent95">95%</div>
		</div>

	</div>{* end quiz div *}

</div>

{else}

<div id="style-profile">
  <div>

	<img src="{$img_ps_dir}survey/survey-signup-hdr.png" alt="{l s='you’re one step closer'}" style="margin:40px 0 0"/>

	{*include file="$tpl_dir./errors.tpl"*}

	<form method="post" id="new_user" class="new_user" action="{$link->getPageLink('stylesurvey.php')}?stp={$step_count}" {*onsubmit="return acceptCGV('{l s='Please accept the terms of service before the next step.' js=1}');"*}>

		{$HOOK_CREATE_ACCOUNT_TOP}
		<fieldset class="medium clearAfter">
			<p>
				<label for="customer_name">{l s='Name'}</label>
				<input type="text" class="sstext" id="customer_name" name="customer_name" value="{if isset($smarty.post.customer_name)}{$smarty.post.customer_name}{elseif isset($fb_cust_name)}{$fb_cust_name}{/if}" />
			</p>
			<p>
				<label for="email">{l s='Email'}</label>
                {if $cemail != ''}
                    <input type="text" id="email_create" readonly="readonly" class="sstext email_exists" name="email" value="{if $cemail==''}{$smarty.post.cemail|escape:'htmlall'|stripslashes}{else}{$cemail}{/if}" />
                    <input type="hidden" name="cemail" value="{if $cemail==''}{$smarty.post.cemail|escape:'htmlall'|stripslashes}{else}{$cemail}{/if}" />
                {else}
                    <input type="text" id="email_create" class="sstext" name="email" value="{if isset($smarty.post.email)}{$smarty.post.email|escape:'htmlall'|stripslashes}{/if}" />
                {/if}
			</p>
			<p>
				<label for="passwd">{l s='Password'}</label>

				<input type="password" class="sstext" name="passwd" id="passwd" />
			</p>
			<p>
				<label for="passwd_confirm">{l s='Password Confirmation'}</label>
				<input type="password" class="sstext" name="passwd_confirm" id="passwd" />
			</p>
		</fieldset>

		<div style="float:left; width:100%">
			<input type="hidden" name="qqa_1000" value="{$smarty.post.qqa_1000}" />
			<input type="hidden" name="qqa_1001" value="{$smarty.post.qqa_1001}" />
			<input type="hidden" name="qqa_1002" value="{$smarty.post.qqa_1002}" />
			<input type="hidden" name="qqa_1003" value="{$smarty.post.qqa_1003}" />
			<input type="hidden" name="qqa_1004" value="{$smarty.post.qqa_1004}" />
			<input type="hidden" name="qqa_1005" value="{$smarty.post.qqa_1005}" />
			<input type="hidden" name="qqa_1006" value="{$smarty.post.qqa_1006}" />
			<input type="hidden" name="qqa_1007" value="{$smarty.post.qqa_1007}" />
			<input type="hidden" name="qqa_1008" value="{$smarty.post.qqa_1008}" />
			{*<input type="hidden" name="qqa_1008_1" value="{$smarty.post.qqa_1008_1}" />
			<input type="hidden" name="qqa_1008_2" value="{$smarty.post.qqa_1008_2}" />
			<input type="hidden" name="qqa_1008_3" value="{$smarty.post.qqa_1008_3}" />
			<input type="hidden" name="qqa_1008_4" value="{$smarty.post.qqa_1008_4}" />
			<input type="hidden" name="qqa_1008_5" value="{$smarty.post.qqa_1008_5}" />
			<input type="hidden" name="qqa_1008_6" value="{$smarty.post.qqa_1008_6}" />
			<input type="hidden" name="qqa_1008_7" value="{$smarty.post.qqa_1008_7}" />
			<input type="hidden" name="qqa_1008_8" value="{$smarty.post.qqa_1008_8}" />
			<input type="hidden" name="qqa_1008_9" value="{$smarty.post.qqa_1008_9}" />
			<input type="hidden" name="qqa_1008_10" value="{$smarty.post.qqa_1008_10}" />
			<input type="hidden" name="qqa_1008_11" value="{$smarty.post.qqa_1008_11}" />
			<input type="hidden" name="qqa_1008_12" value="{$smarty.post.qqa_1008_12}" />*}
			<input type="hidden" name="qqa_1009_1" value="{$smarty.post.qqa_1009_1}" />
			<input type="hidden" name="qqa_1009_2" value="{$smarty.post.qqa_1009_2}" />
			<input type="hidden" name="qqa_1009_3" value="{$smarty.post.qqa_1009_3}" />
			<input type="hidden" name="qqa_1009_4" value="{$smarty.post.qqa_1009_4}" />
			<input type="hidden" name="qqa_1009_5" value="{$smarty.post.qqa_1009_5}" />
			<input type="hidden" name="qqa_1009_6" value="{$smarty.post.qqa_1009_6}" />
			<input type="hidden" name="qqa_1009_7" value="{$smarty.post.qqa_1009_7}" />
			<input type="hidden" name="qqa_1009_8" value="{$smarty.post.qqa_1009_8}" />
			<input type="hidden" name="qqa_1009_9" value="{$smarty.post.qqa_1009_9}" />
			<input type="hidden" name="qqa_1009_10" value="{$smarty.post.qqa_1009_10}" />
			<input type="hidden" name="qqa_1009_11" value="{$smarty.post.qqa_1009_11}" />
			<input type="hidden" name="qqa_1009_12" value="{$smarty.post.qqa_1009_12}" />
			{*<input type="hidden" name="qqa_1009_13" value="{$smarty.post.qqa_1009_13}" />
			<input type="hidden" name="qqa_1009_14" value="{$smarty.post.qqa_1009_14}" />
			<input type="hidden" name="qqa_1009_15" value="{$smarty.post.qqa_1009_15}" />
			<input type="hidden" name="qqa_1009_16" value="{$smarty.post.qqa_1009_16}" />*}
			<input type="hidden" name="qqa_1010" value="{$smarty.post.qqa_1010}" />
			<input type="hidden" name="qqa_1011" value="{$smarty.post.qqa_1011}" />
			<input type="hidden" name="qqa_1012" value="{$smarty.post.qqa_1012}" />

			<input type="hidden" name="ref_by" value="{if isset($smarty.post.ref_by)}{$smarty.post.ref_by|escape:'htmlall':'UTF-8'}{/if}" />
			<input type="submit" name="{if $cemail != '' || isset($smarty.post.cemail)}updateAccount{else}submitAccount{/if}" id="submitAccount" class="buttonlarge blue" value="{l s='hemen katil'}" />
		</div>
		{$HOOK_CREATE_ACCOUNT_FORM}

	</form>
	<img src="{$img_ps_dir}survey/survey-signup-ftr.png" alt="{l s='you’re one step closer'}" style="margin:0 0 0 0"/>

  </div>
</div>

{/if}
