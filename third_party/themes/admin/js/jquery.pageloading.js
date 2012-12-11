jQuery.PadeLoading = function (Options) 
{
	// Default Settings
	var Settings = { 
        'BgIMG' : '',
        'BgColor' : '#000000',
        'BgInnerColor' : '#FFFFFF',
        'BorderColor' : '#000000',
        'ShadowColor' : '',
        'LoaderIMG' : 'LoaderIMG.gif',
        'zIndex' : 99
    };
	
	// If options exist, replace the default Settings by the options set.
	if (Options) 
	{ 
		$.extend(Settings, Options);
	}
	
	function LoadingPage()
	{
		if(Settings.LoaderIMG != "")
		{
			$("body").append("<div id=\"OverMask\"></div><div id=\"LoaderDiv\"></div>");
            $("#LoaderDiv").html( $("#AjaxLoadImg").html() );
			
			$("#OverMask").css({
                'width' : '100%',
                'height' : '100%',
                'background-color': Settings.BgColor,
                'position': 'absolute',
                'left' : 0, 
                'top' : 0, 
                'position' : 'fixed',
                'opacity': 0.5,
                "z-index" : Settings.zIndex
            }).show();
			
			var Red, Green, Blue;
			if(Settings.BorderColor != "")
			{
				Red = HexToRed(Settings.BorderColor);
				Green = HexToGreen(Settings.BorderColor);
				Blue = HexToBlue(Settings.BorderColor);
			}
			else
			{
				Red = 0;
				Green = 0;
				Blue = 0;
			}

            $("#LoaderDiv").hide().css({
                'position': 'fixed',
                'left' : '50%', 
                'top' : '50%', 
                'margin-top' : -47 , 
                'margin-left' : -47, 
                'z-index' : Settings.zIndex + 1,
                'background-color': Settings.BgInnerColor,
                '-moz-background-clip' : 'padding',
                '-webkit-background-clip' : 'padding',
                'background-clip' : 'padding-box',
                'border' : '10px solid rgba(' + Red + ', ' + Green + ', ' + Blue + ', 0.3)',
                '-webkit-border-radius' : '13px',
                '-moz-border-radius' : '13px',
                'border-radius' : '13px',
                '-webkit-box-shadow' : '0px 0px 5px' + Settings.ShadowColor,
                '-moz-box-shadow' : '0px 0px 5px' + Settings.ShadowColor,
                'box-shadow' : '0px 0px 5px' + Settings.ShadowColor, 
                'padding' : '5px'
                
            }).fadeIn(400);
		}
		else
		{
			alert("The LoaderIMG setting can not be empty !");
		}
	}
	
	function HexToRed(Value) 
	{ 
		return parseInt((CutDiese(Value)).substring(0,2),16);
	}
	function HexToGreen(Value) 
	{ 
		return parseInt((CutDiese(Value)).substring(2,4),16);
	}
	function HexToBlue(Value) 
	{ 
		return parseInt((CutDiese(Value)).substring(4,6),16);
	}
	function CutDiese(Value) 
	{ 
		return (Value.charAt(0)=="#") ? Value.substring(1,7) : Value;
	}
		
	$(function()
	{
		LoadingPage();
	});
	
}

jQuery.PadeLoaded = function () 
{
	$("#LoaderDiv").fadeOut(100, function()
	{
        $("#LoaderDiv").remove();
		$("#OverMask").fadeOut(100);
        $("#OverMask").remove();
	});
}