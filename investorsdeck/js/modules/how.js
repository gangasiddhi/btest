//external dependencies
if($ && Pitchdeck)
{
	/**
	 * 
	 */
	Pitchdeck.attach('team',
	{
		init: function()
		{
			//set initial state
			Pitchdeck._('team').animate(0);
		},
		
		functions:
		{
			run: function(p,frame)
			{	
				for (var id in frame)
				{
					
					if (typeof(frame[id]) == 'object')
					{
						$(id).css(frame[id]);
					} else if (typeof(frame[id]) == 'function')
					{
						frame[id](p,id);
					}
				}
			},
			
		},
		
		animate: function(p)
		{
			var kf = Pitchdeck._('team').keyframes;
			
			for (var i in kf)
			{
				if (i-p >= 0)
				{
					Pitchdeck._('team').functions.run(p,kf[i]);
					break;
				}
			}
		},
		
		keyframes:
		{	

		},
		
		events:
		{

		},
	});
}
