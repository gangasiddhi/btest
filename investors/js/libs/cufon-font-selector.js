Selector = {

	run: function()
	{

		require(Selector.fonts,function()
		{
			for(var font in Selector.selectors)
			{
				for (var i in Selector.selectors[font])
				{
					Cufon.replace(Selector.selectors[font][i],{fontFamily:font});
				}
			}
		});
	},

	fonts:
	[
		'libs/GothamBold_400.font',
		'libs/GothamBook_325.font',
		'libs/GothamMedium_350.font'
	],

	selectors:
	{
		'GothamBold':
		[
			'.content-title-description h2, \n\
			#market .columns-container .column ul li p, \n\
			#problem .columns-container .column ul li, #problem .content .problem-footer .problem-footer-title, \n\
			#solution .solution-container .solution .title-container p, \n\
			#planning .content-title-description p,\n\
			#contact #contact-footer p.footer-title, \n\
			#product .product-container .product .title-container, #product .content .product-footer .product-footer-title, #planning .planning-container .planning .data-container .spl, \n\
			#kpi .kpi-container .bottom-container .data-container.kpi-footer p,\n\
			#how #how-slide h3,\n\
			#product .content .product-footer .product-footer-notes,\n\
			footer p'
		],

		'GothamMedium':
		[
			'h1.text-logo-with-heart, header .content .ui-menu ul li'
		],

		'GothamBook':
		[
			'.content-title-description p, .content-title-description h3\n\
			,#market .columns-container .column ul li, #team .team-container .info\n\
			,#welcome .intro-text h3, #welcome p\n\
			,#problem .columns-container .column ul li ul li\n\
			,#solution .solution-container .solution .data-container p\n\
			,#kpi .kpi-month, #kpi .kpi-container .data-container .number, #kpi .kpi-container .data-container .label\n\
			,#kpi .kpi-container .bottom-container .data-container.kpi-footer p\n\
			,#kpi .content .content-data-container .row .left\n\
			,#kpi .content .content-data-container .row .middle p\n\
			,#kpi .content .content-data-container .row .right\n\
			,#contact p.first-paragraph-padded, #contact .content p.cheers, #contact #contact-footer p\n\
			,#planning .planning-container .planning .title-container p, #planning .planning-container .planning .data-container p, #product .product-container .product .title-number, #product .product-container .product .data-container\n\
			,#product .content .product-footer .product-footer-notes\n\
			,#product .content .product-footer .product-footer-data, #how #how-slide p'
		]
	}

};
