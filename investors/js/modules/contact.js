//external dependencies
if($ && Pitchdeck)
{
	/**
	 * 
	 */
	Pitchdeck.attach('contact',
	{
		init: function()
		{
			//set initial state
			Pitchdeck._('contact').animate(0);
		},
		
		functions:
		{
			run: function(frame)
			{
				for (var id in frame)
				{
					$(id).css(frame[id]);
				}
			}
		},
		
		animate: function(p)
		{
			var kf = Pitchdeck._('contact').keyframes;
			
			for (var i in kf)
			{
				if (i-p >= 0)
				{
					Pitchdeck._('contact').functions.run(kf[i]);
					break;
				}
			}
		},
		
		keyframes:
		{
			
			0:
			{
				'#contact .content .content-title-description h2':
				{
					/*'animation': 'blink 1s step-end infinite',
					'-moz-animation': 'blink 1s step-end infinite',
					'-webkit-animation': 'blink 1s step-end infinite'*/
				}
			},
			
			20:
			{
				'#contact .content .content-title-description h2':
				{
				
				}
			},
			
			40:
			{
				'#contact .content .content-title-description h2':
				{
					
				}
			},
			
			60:
			{
				'#contact .content .content-title-description h2':
				{
					
				}
			},
			
			80:
			{
				'#contact .content .content-title-description h2':
				{
					
				}
			},
			
			100:
			{
				'#contact .content-title-description h2':
				{
					
				}
			}
		},
		
		events:
		{
			
		},
		
		current_toggled: $({})
	});
}
