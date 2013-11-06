$(document).ready( function() {
    $("div.inner").codaSlider();
    $(".panelContainer").css("visibility","visible");
    $("div.inner").css({
        height: $('div.panel:eq(0)').height()
    });
    $(".celeb").css("display","none");
    $(".celeb").fadeIn("slow");
});

function scroll_to_pane(pagenum) {
    $("#quiz").find(".stripNav ul li a:eq(" + (pagenum-1) + ")").trigger('click');
    toggle_rows('status', pagenum, 11);
}

function check_page(pagenum, total) {
	var is_ok1 = true, is_ok2 = true, is_ok3 = true;
	var i;

	if (/*pagenum == 9 ||*/ pagenum == 10) {
		is_ok1 = false;
		for (i = 1; i <= total; i++) {
			if($('#q' + pagenum + '_' + i).hasClass('selected')) {
				is_ok1 = true;
				break;
			}
		}

		if (!is_ok1) {
			$("#missing_answer_" + pagenum).css("display","");
			$("#question_text_100" + (pagenum-1)).css("color", "red");
		} else {
			$("#missing_answer_" + pagenum).css("display","none");
			$("#question_text_100" + (pagenum-1)).css("color", "");
		}
	} else if (pagenum == 11) {
		is_ok1 = false, is_ok2 = false, is_ok3 = false;
		for (i = 1; i <= 5; i++) {
			if($('#qsp_' + i).hasClass('selected')) {
				is_ok1 = true;
				break;
			}
		}
		for (i = 1; i <= 8; i++) {
			if($('#q11_' + i + ' a').hasClass('selected')) {
				is_ok2 = true;
				break;
			}
		}
		for (i = 1; i <= 5; i++) {
			if($('#q12_' + i + ' a').hasClass('selected')) {
				is_ok3 = true;
				break;
			}
		}

		if (!is_ok1) {
			$("#missing_answer_" + pagenum).css("display","");
			$("#question_text_1010, #question_text_1010_1").css("color", "red");
		} else if (!is_ok2) {
			$("#missing_answer_" + pagenum).css("display","");
			$("#question_text_1010, #question_text_1010_2").css("color", "red");
			$("#question_text_1010_1").css("color", "");
		} else if (!is_ok3) {
			$("#missing_answer_" + pagenum).css("display","");
			$("#question_text_1010, #question_text_1010_3").css("color", "red");
			$("#question_text_1010_1, #question_text_1010_2").css("color", "");
		} else {
			$("#missing_answer_" + pagenum).css("display","none");
			$("#question_text_1010, #question_text_1010_1, #question_text_1010_2, #question_text_1010_3").css("color", "");
		}
	}

    if (is_ok1 && is_ok2 && is_ok3) {
        if (pagenum < 11) {
            scroll_to_pane(pagenum+1);
        } else {
			$("#cact").val('1');
            $("#quiz_form").get(0).submit();
        }
    }
    return false;
}

function toggleSingleChoiceSurvey(div, pos, val, ques, total) {
	for (var i = 1; i <= total; i++) {
		var div_id = '#q' + div + '_' + i + ' a';
		if (pos == i) {
			$(div_id).addClass('selected');
			$('#q_' + ques).val(val);
		} else {
			$(div_id).removeClass('selected');
		}
	}
}

function toggleAgeChoiceSurvey(pos, val, ques, total) {
	for (var i = 1; i <= total; i++) {
		var a_id = '#qsp_' + i;
		if (pos == i) {
			$(a_id).addClass('selected');
			$('#q_' + ques).val(val);
		} else {
			$(a_id).removeClass('selected');
		}
	}
}

function toggleMultiChoiceSurveySelect(div, pos, val, ques, total) {
	for (var i = 1; i <= total; i++) {
		var div_id = '#q' + div + '_' + i;
		if (pos == i) {
			if($(div_id).hasClass('selected')) {
				$(div_id).removeClass('selected');
				$('#q_' + ques + '_' + pos).val('');
			} else {
				$(div_id).addClass('selected');
				$('#q_' + ques + '_' + pos).val(val);
			}
			break;
		}
	}
}

function hideErrors() {
	$("#errorExplanation").hide();
	return true;
}
