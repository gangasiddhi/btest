function faqEdition(faqId)
{
	getE('id').value = faqId;
	var cend = parseInt(getE('languageNb').value - 1);
	for (var i=0; i<=cend; i++) {
		getE('questionInput_' + faqs[faqId][i * 3]).value = faqs[faqId][i * 3 + 1];
		tinyMCE.get('answerInput_' + faqs[faqId][i * 3]).setContent(faqs[faqId][i * 3 + 2]);
	}
	getE('submitFaqUpdate').disabled = '';
	getE('submitFaqAdd').disabled = true;
	getE('submitFaqAdd').setAttribute('class', 'button disable');
	getE('submitFaqUpdate').setAttribute('class', 'button');
	/* ##### IE */
	getE('submitFaqAdd').setAttribute('className', 'button disable');
	getE('submitFaqUpdate').setAttribute('className', 'button');
}

function faqDeletion(faqId)
{
	document.location.replace(currentUrl+'&id='+faqId+'&token='+token);
}