//external dependencies
if($ && Pitchdeck)
{
	/**
	 * 
	 */
	Pitchdeck.attach('solution',
	{
		init: function()
		{
			//set initial state
			Pitchdeck._('solution').animate(0);
		},
		
		functions:
		{
			run: function(frame)
			{
				for (var id in frame)
				{
					$(id).css(frame[id]);
				}
			},
		},
		
		animate: function(p)
		{
			var kf = Pitchdeck._('solution').keyframes;
			
			for (var i in kf)
			{
				if (i-p >= 0)
				{
					Pitchdeck._('solution').functions.run(kf[i]);
					break;
				}
			}
		},
		
		keyframes:
		{
			0:
			{
				'#solution .solution-container .solution .title-container p, #solution .solution-container .solution .data-container, #solution .solution-container .solution .data-container':
				{
					opacity:0
				}
				
			},
			
			20:
			{
				'#solution .solution-container .solution .title-container p, #solution .solution-container .solution .data-container, #solution .solution-container .solution .data-container':
				{
					opacity:0
				}
			},
			
			40:
			{
				'#solution .solution-container .solution .title-container p, #solution .solution-container .solution .data-container, #solution .solution-container .solution .data-container':
				{
					opacity:0
				}
			},
			
			60:
			{
				'#solution .solution-container .solution .title-container p, #solution .solution-container .solution .data-container, #solution .solution-container .solution .data-container':
				{
					opacity:0
				}
			},
			
			80:
			{
				'#solution .solution-container .solution .title-container p, #solution .solution-container .solution .data-container, #solution .solution-container .solution .data-container':
				{
					opacity:1
				}
			},
			
			100:
			{
				'#solution .solution-container .solution .title-container p, #solution .solution-container .solution .data-container':
				{
					'-moz-transition-delay': '.8s',
					opacity:1
				},
				'#solution .solution-container .solution .data-container':
				{
					'-moz-transition-delay': '.8s',
					opacity:1
				}
			}
		},
		
		events:
		{
			
		},
		
		current_toggled: $({})
	});
}
