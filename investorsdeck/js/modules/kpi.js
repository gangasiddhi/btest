//external dependencies
if($ && Pitchdeck)
{
	/**
	 * 
	 */
	Pitchdeck.attach('kpi',
	{
		init: function()
		{
			//set initial state
			Pitchdeck._('kpi').animate(0);
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
			var kf = Pitchdeck._('kpi').keyframes;
			
			for (var i in kf)
			{
				if (i-p >= 0)
				{
					Pitchdeck._('kpi').functions.run(kf[i]);
					break;
				}
			}
		},
		
		keyframes:
		{
			0:
			{
				'#kpi .kpi-container .top-container .data-container':
				{
					//marginBottom:'-195%'
					//opacity:1
					'-moz-transform': 'rotateY(0deg)',
					'-webkit-transform':'rotateY(0deg)'
				},
				'#kpi .kpi-container .bottom-container .data-container':
				{
					//marginBottom:'-195%'
					//opacity:1
					'-moz-transform': 'rotateY(0deg)',
					'-webkit-transform':'rotateY(0deg)'
				}
			},
			
			20:
			{
				'#kpi .kpi-container .top-container .data-container':
				{
					//marginBottom:'-155%'
					//opacity:1
					'-moz-transform': 'rotateY(60deg)',
					'-webkit-transform':'rotateY(60deg)'
				},
				'#kpi .kpi-container .bottom-container .data-container':
				{
					//marginBottom:'-155%'
					//opacity:1
					'-moz-transform': 'rotateY(60deg)',
					'-webkit-transform':'rotateY(60deg)'
				}
				
			},
			
			40:
			{
				'#kpi .kpi-container .top-container .data-container':
				{
					//marginBottom:'-115%'
					//opacity:1
					'-moz-transform': 'rotateY(180deg)',
					'-webkit-transform':'rotateY(180deg)'
				},
				'#kpi .kpi-container .bottom-container .data-container':
				{
					//marginBottom:'-155%'
					//opacity:1
					'-moz-transform': 'rotateY(180deg)',
					'-webkit-transform':'rotateY(180deg)'
				}
			},
			
			60:
			{
				'#kpi .kpi-container .top-container .data-container':
				{
					//marginBottom:'-75%'
					//opacity:1
					'-moz-transform': 'rotateY(240deg)',
					'-webkit-transform':'rotateY(240deg)'
				},
				'#kpi .kpi-container .bottom-container .data-container':
				{
					//marginBottom:'-155%'
					//opacity:1
					'-moz-transform': 'rotateY(240deg)',
					'-webkit-transform':'rotateY(240deg)'
				}
				
			},
			
			80:
			{
				'#kpi .kpi-container .top-container .data-container':
				{
					//marginBottom:'-35%'
					//opacity:1
					'-moz-transform': 'rotateY(300deg)',
					'-webkit-transform':'rotateY(300deg)'
				},
				'#kpi .kpi-container .bottom-container .data-container':
				{
					//marginBottom:'-155%'
					//opacity:1
					'-moz-transform': 'rotateY(300deg)',
					'-webkit-transform':'rotateY(300deg)'
				}
			},
			
			100:
			{
				'#kpi .kpi-container .top-container .data-container':
				{
					'-moz-transform': 'rotateY(360deg)',
					'-webkit-transform':'rotateY(360deg)',
					opacity:1
				},
				'#kpi .kpi-container .bottom-container .data-container':
				{
					//marginBottom:'-155%'
					//opacity:1
					'-moz-transform': 'rotateY(360deg)',
					'-webkit-transform':'rotateY(360deg)'
				}
			}
		},
		
		events:
		{
			/*'#kpi .kpi-container .bottom-container .data-container .number':
			{
				hover:
				[
					function()
					{
						
						$(this).numAnim({
							endAt: $(this).attr('num'),
							duration: 3
						});
						Cufon.refresh(this);
					}
				]
			}*/
		},
		
		current_toggled: $({})
	});
}
