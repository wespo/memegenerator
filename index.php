<html>
	<head>
		<meta charset="UTF-8">
		<title>Important thought generator.</title>
		
		<meta name="description" content="Rainbow Dash, of my little pony, has important thoughts" />
		<meta name="author" content="TheJBW" />
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
		<link rel="icon" href="favicon.ico" type="image/x-icon"/>
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
		<link href='https://fonts.googleapis.com/css?family=Bitter' rel='stylesheet' type='text/css'>
		<link href='https://fonts.googleapis.com/css?family=Alegreya+Sans' rel='stylesheet' type='text/css'>
		<style>
			body { font-family: 'Alegreya Sans', sans-serif; font-size:1.5em; } 
			h1 {font-family: 'Bitter', serif; }
			.io { font-family: 'Alegreya Sans', sans-serif; font-size:.8em}
			.thumb {margin: 2px; border: 1px red solid}
			.horizontalGallery {width:1000px; height: 100px; position: relative;}
			.verticalGallery {width:100px; height: 1000px; position: relative; top:100px; left:1000px;}
			.preview {visibility: invisible}
			.note{font-size: 0.5em; color: grey;}
		</style>
		<script src="jquery.min.js"></script>
		<script type="text/javascript">
			window.onload = function() {
				paintBackground();
			}
			function paintBackground() //initial paint function.
			{
				
				//initialize the canvas
				var comicPane = document.getElementById("watermarkCanvas");
				var context = comicPane.getContext("2d");
				comicPane.width = 1000;
				comicPane.height = 500;
				context.fillStyle="#FFFFFF";
				context.fillRect(0,0,comicPane.width,comicPane.height);
				var defImage = new Image();
				defImage.src = "stare.jpg";

				//when image is loaded, draw.
				defImage.onload = function()
				{
					comicPane.width = defImage.width;
					comicPane.height = defImage.height;
					context.drawImage(defImage, comicPane.width/2 - defImage.width/2, comicPane.height/2 - defImage.height/2);
				};	

				//set up "download" button
				var download = document.getElementById('img-download');
				download.addEventListener('click', prepareDownload, false);

				function prepareDownload()
				{
					var data = comicPane.toDataURL();
					download.href = data;
				}	

				//initialized thumbnail gallery.
				updateThumbs(null);			
				generateBestOf();
			}

			//global access to current image dom element, some flags.
			var bg = new Image();
			var isComicPanelInitialized = false;
			var isUploaded = true;

			function updateCanvas() //picks an image from the list, draws canvas when image is ready.
			{
				var comicPane = document.getElementById("watermarkCanvas");
				var context = comicPane.getContext("2d");
				//get list of available images from PHP. May be preferable long term to make serverlet to serve a random filename instead.
				var images =  '<?php 
							 $thelist = "";
							 if ($handle = opendir('backgrounds')) {
							   $images = array();
							   while (false !== ($file = readdir($handle))) {
							      if ($file != "." && $file != "..") {
							            array_push($images, $file);
							      }
							   }
							   closedir($handle);
							   echo json_encode($images);
							 }
							?>'
				images = JSON.parse(images);
				bg.src = 'backgrounds/' + images[Math.floor(Math.random()*images.length)];
				bg.onload = function() {
					drawCanvas();
				};
			};

			function drawCanvas() //draws the image on the canvas.
			{
				if(isComicPanelInitialized == false) //first run.
				{
					updateCanvas();
					isComicPanelInitialized = true;
					return;
				}
				var comicPane = document.getElementById("watermarkCanvas");
				var context = comicPane.getContext("2d");
				if(bg.width > bg.height) //adjust so that it scales wide images to 1000px wide or tall ones to 500px tall
					{
						comicPane.width = 1000;
						comicPane.height = bg.height * comicPane.width/bg.width;
					}
					else
					{
						comicPane.height = 500;
						comicPane.width = bg.width * comicPane.height/bg.height;
						
					}
					//draw images.
					context.fillStyle="#FFFFFF";
					context.fillRect(0,0,comicPane.width,comicPane.height);
					context.drawImage(bg, 0, 0,comicPane.width,comicPane.height);

					// Set the text style to that to which we are accustomed
				    context.lineWidth  = 5;
				    var fontHeight = 20;
				    context.font = fontHeight + 'pt Bitter';
				    context.strokeStyle = 'black';
				    context.fillStyle = 'white';
				    context.textAlign = 'center';
				    context.lineJoin = 'round';

					// Draw the text
					var text = document.getElementById('custom-text').value;
					//text = text.toUpperCase(); //all caps mode
					lines = getLines(context,text,comicPane.width * 0.8, context.font); //break up text across several lines so it doesn't ooverflow.
					var lineHeight = fontHeight + 5; //set up pointer to correct position on image
					var textHeight = lines.length * lineHeight;
					if(getPosition() == 0.95){
						var textBottom = comicPane.height;
						var textTop = textBottom - textHeight + lineHeight/2;
					}
					else
					{
						var textTop = comicPane.height * 0.05;
					}
					

					var x = comicPane.width * 0.5;
					var y = textTop;
					for (var line of lines) //draw the lines
					{
						drawLine(context,line,x,y);
						y = y + lineHeight;
					}
					isUploaded = false; //flag so you can't spam the same image a bunch of times
			}


			function drawLine(ctx, phrase, x, y) //draw one line of text on the canvas.
			{
				ctx.strokeText(phrase,x,y);
	    		ctx.fillText(phrase,x,y);
			}

			/**
		     * Divide an entire phrase in an array of phrases, all with the max pixel length given.
		     * The words are initially separated by the space char.
		     * @param phrase
		     * @param length
		     * @return
		     */
			function getLines(ctx,phrase,maxPxLength,textStyle) {
			    var wa=phrase.split(" "),
			        phraseArray=[],
			        lastPhrase=wa[0],
			        measure=0,
			        splitChar=" ";
			    if (wa.length <= 1) {
			        return wa
			    }
			    ctx.font = textStyle;
			    for (var i=1;i<wa.length;i++) {
			        var w=wa[i];
			        measure=ctx.measureText(lastPhrase+splitChar+w).width;
			        if (measure<maxPxLength) {
			            lastPhrase+=(splitChar+w);
			        } else {
			            phraseArray.push(lastPhrase);
			            lastPhrase=w;
			        }
			        if (i===wa.length-1) {
			            phraseArray.push(lastPhrase);
			            break;
			        }
			    }
			    return phraseArray;
			}


			function getPosition() //gets and parses position radio button. Foolishly defined with a fixed position...
			{
				var off_payment_method = document.getElementsByName('position');
				for ( var i = 0; i < off_payment_method.length; i++) 
				{
    				if(off_payment_method[i].checked) 
    				{
        				if(off_payment_method[i].value == "top")
        				{
        					return 0.05;
        				}
        				else
        				{
        					return 0.95;
        				}
        			}
        		}
        		return 0.95;
        	}

        	      $('.imgur-submit').html('Uploading...')

        function enableButton() //enables the upload to imgur button. There is a timeout to discourage spamming.
        {
			var sendButton = document.getElementById("imgurButton"); 
			sendButton.disabled = false;
        }
       	function uploadToImgur(){ //code to send an image to imgur. Not my api key.
			// this is all standard Imgur API; only LC-specific thing is the image
			// data argument;
			if(isUploaded)
			{
				return;
			}

			var sendButton = document.getElementById("imgurButton");
			sendButton.disabled = true;
			setTimeout(enableButton, 8000);
			var comicPane = document.getElementById("watermarkCanvas");
			var imgSend = comicPane.toDataURL('image/png').split(',')[1];
			$.ajax({
			    url:'https://api.imgur.com/3/upload',
			    type: 'POST',
			    data: {
			      image:imgSend,
			      type: 'png',
			      title: 'image'
			    },
			    beforeSend: function(xhr){
			    	
			    	xhr.setRequestHeader('Authorization', 'Client-ID f7c154b2c7973e7')
			      //xhr.setRequestHeader('Authorization', 'Client-ID 469226b25b097ff') //old not mine
			    },
			    success: function(data) {
			      var imgUrl = data.data.link;
			      var imgurLink = document.getElementById("imgurURL");
			      imgurLink.href = imgUrl;
			      imgurLink.textContent = 'imgur link';
			      isUploaded = true;
			      updateThumbs(data.data.id);
			    },
			    error: function(data) {
			      console.log(data);
			    }
			  });
		};

		function updateThumbs(newImage){
			//then get list of new thumbnails, send old image if applicable. On successs continue.
			if(newImage == null)
			{
				$.get("image.php").done(recentThumbs);
			}
			else //no new image
			{
				$.get("image.php",'imgurURL='+newImage).done(recentThumbs);
			}

		}

		function generateBestOf(){
			$.get("image.php",'bestof').done(bestThumbs);
		}

		function recentThumbs(thumbstring)
		{
			thumbDOM(thumbstring,"thumbs");
		}
		function bestThumbs(thumbstring)
		{
			thumbDOM(thumbstring,"bestof");
		}

		function thumbDOM(thumbstring, node)
		{
			var xOffset = 10;
			var yOffset = 10;
			thumbWidth = 0;
			thumbHeight = 0;
			var thumbs = JSON.parse(thumbstring);
			//first clear out existing thumnail div
			var myNode = document.getElementById(node);
			while (myNode.firstChild) {
			    myNode.removeChild(myNode.firstChild);
			}
		

			//then populate thumbnails with pictures
			var baseURL = 'http://i.imgur.com/';
			var extension = '.png';
			
			for(var thumb of thumbs) //generate thumbs
			{
				var a = document.createElement('a');
				a.setAttribute('href', baseURL + thumb + extension);
				a.target = "_blank";
				var thumObj = new Image;
				thumObj.src = baseURL + thumb + 's' + extension;
				a.appendChild(thumObj);
				thumObj.bigthumb = baseURL + thumb + 'l' + extension;
				thumObj.setAttribute("class", "thumb");
				myNode.appendChild(a);
				$(thumObj).hover(function(e){
					this.t = this.title;
					this.title = "";	
					var c = (this.t != "") ? "<br/>" + this.t : "";
		

					var p = document.createElement('p');
					p.id = 'preview'
					
					var bigThumb = new Image();
					bigThumb.src = this.bigthumb;
					bigThumb.setAttribute("class", "thumb");
					bigThumb.onload = function(){
						thumbHeight = bigThumb.height;
						thumbWidth = bigThumb.width;
					};
					bigThumb.alt = 'Image Preview'
					p.appendChild(bigThumb);


					//$("body").append("<p id='preview'><img src='"+  +"' alt='Image preview' />"+ c +"</p>");
					$("body").append(p);
					$("#preview")
						.css("position","absolute")
						.css("top",(e.pageY - yOffset - thumbHeight) + "px")
						.css("left",(e.pageX + xOffset) + "px")
						.fadeIn("fast");						
			    },
				function(){
					this.title = this.t;	
					$("#preview").remove();
			    });	
				$(".thumb").mousemove(function(e){
					$("#preview")
						.css("position","absolute")
						.css("top",(e.pageY - yOffset - thumbHeight) + "px")
						.css("left",(e.pageX + xOffset) + "px");
				});			
			}
		}
				

				/* CONFIG */
		

		// these 2 variable determine popup's distance from the cursor
		// you might want to adjust to get the right result
		
	/* END CONFIG */
	$(".thumb")


	</script>
	</head>
	<body><center>
				<h1>Rainbow Dash Has An Important Thought</h1>
				<canvas id="watermarkCanvas" width="1024" height="600"style="border:1px red solid;"></canvas>
				<p>
					<textarea id="custom-text" class="io" type="textarea" style="border:1px red solid;" rows="4" cols="104">enter some text</textarea><br>
					 <form>
						<input type="radio" name="position" onchange="drawCanvas()" value="top">Top 
						<input type="radio" name="position" onchange="drawCanvas()" value="bottom" checked>Bottom
						<button type="button" class="io" id="addImage" onclick="updateCanvas()">Generate Thought</button>
						<a id="img-download" class="io" download="deep_thought.png" href="">Download Image</a>
						<input type="button" id="imgurButton" class="io" onclick="uploadToImgur()" value="Upload to Imgur">
						<a href="" class="io" target="_blank" id="imgurURL"></a>
					</form>
				</p>
				<p>
					<h4>Rainbow's Most Recent Important Thoughts</h4>
					<div id="thumbs" class="horizontalGallery"></div>
					<h4>Rainbow's Favorite Important Thoughts</h4>
					<div id="bestof" class="horizontalGallery"></div>
				</p>
				<p>
					<a href="http://sscs6000.com" class="io" target="_blank">The Original SSCS</a><br>
					<span class="note">Special thanks to /u/LunarWolves who stayed up late one night testing and providing feedback. Your support means that at least one person will be amused by this thing. <img src="luna.png"/></span><br>
					<span class="note">Site Copyright 2016, intended as a parody work, all images and characters copyright their respective owners.</span>
				</p>

				
				

	</center></body>
</html>