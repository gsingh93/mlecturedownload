<?php
require_once "../resource/db_config.php";

$mysqli = new mysqli($host, $user, $password, $database);
if ($mysqli->connect_errno) {
	echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	//echo "The EC2 server this site runs on has crashed. This site will be back up tomorrow.";
}

$result = $mysqli->query("SELECT * FROM download_count");
$result->data_seek(0);
$result = $result->fetch_assoc();
$count = $result['count'];

$invalid = false;
if (array_key_exists('url', $_POST)) {
   $url = trim($_POST['url']);

   if (!validate($url)) {
     $invalid = true;
   }

   if (!$invalid) {
	   if (substr($url, 0, 5) == 'https') {
	     $url = 'http' . substr($url, 5);
	   }
	
	   $ids = get_ids($url);
	   $video_path = get_video_path($ids[0], $ids[1]);
	   
	   if (!$mysqli->query("INSERT INTO stats() VALUES()")) {
	   	   echo "Insertion failed: (" . $mysqli->errno . ") " . $mysqli->error;
	   }
	   
	   if (!$mysqli->query("UPDATE download_count SET count=" . ++$count)) {
	   		echo "Update failed: (" . $mysqli->errno . ") " . $mysqli->error;
	   }
	}
}
function validate($url) {
   if (preg_match('#^https?:\/\/inst-tech\.engin\.umich\.edu\/leccap\/view\/[a-z0-9]+\/[0-9]+\/?$#', $url) === 1) {
     return true;
   } else {
     return false;
   }
}

function get_ids($url) {
  $matches = array();
  preg_match('/info\/([0-9a-z]*)\/([0-9]*)/', file_get_contents($url), $matches);
  unset($matches[0]);
  $ids = array_values($matches);
  return $ids;
}

function get_video_path($id1, $id2) {
  $BASE_PATH = '/leccap/product/info/';
  $BASE_URL = 'http://inst-tech.engin.umich.edu';

  $json = json_decode(file_get_contents($BASE_URL . $BASE_PATH . $id1 . '/' . $id2));
  return  $BASE_URL . substr($json->xmlURL, 0, strlen($json->xmlURL) - 3) . 'mp4';
}
?>
<!DOCTYPE html>
<html>
  <head>
  	<title>MLecture Download</title>
  	
  	<meta http-equiv="X-UA-Compatible" content="IE=9" />
	<meta property="og:image" content="http://mlecturedownload.com/images/M_facebook_icon.png" />
	<meta property="og:description" content="Download your lectures!" />
	<meta property="og:title" content="MLecture Download" />
	<meta property="og:url" content="http://www.mlecturedownload.com" />
	<meta property="og:site_name" content="MLecture Download" />  	

	<link rel="icon" type="image/ico" href="http://mlecturedownload.com/favicon.ico">
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js" type="text/javascript"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
    <script src="js/jquery.easing.1.3.js" type="text/javascript"></script>
	<script src="js/jquery.flipCounter.1.2.pack.js" type="text/javascript"></script>
	
    <script type="text/javascript">

      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-31655027-1']);
      _gaq.push(['_setDomainName', 'gulshansingh.com']);
      _gaq.push(['_trackPageview']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();

    </script>
  </head>
  <body>
    <div id="header">
      <img src="images/logo.png" alt="mlecturedownload">
    </div>
    <div id="wrapper">
      <div id="instructions">

	Enter the URL for a CAEN Lecture Recording.<br>
	The format for the URL should be:<br>
	<span style="font-weight: bold;font-family: Courier New">https://inst-tech.engin.umich.edu/leccap/view/[some numbers and letters]/[some numbers]</span>.<br>
    An sample URL is <span style="font-weight: bold;font-family: Courier New">http://inst-tech.engin.umich.edu/leccap/view/zyfkpqdwb54rrjs5roe/18706</span>
      </div>
      <form method="post" id="url-form"> 
		URL: <input type="text" name="url" id="url">
		<input type="submit" value="Download!" class="button" id="submit">
      </form>

      <script>
		// Initialize input box animations
	        $("#url").on("focus", function(event) {
	          $(this).animate({width:'450px'});
		});
		$("#url").on("blur", function(event) {
	          $(this).animate({width:'200px'});
		});
      </script>


      <?php
      	if (isset($video_path)):
      		echo "<div id='result'>";
      		echo "<a class='button' id='video-url' target=\"_blank\" href=\"$video_path\">Watch Video</a><br>";
      ?>
      <div id="download-instructions">
	To download the video, right click on the link and select "Save As".<br>
	The ability to download all of the lectures for a class at once is coming soon!<br>
	</div>
	<div id="speed-instructions-toggle">
		<img class="right-arrow" src="images/right_arrow.png">I'm a beast and can understand my lectures at twice the speed. How can I do watch them at that speed?
	</div>
	<div id="speed-instructions">
		<span class="number">1.</span> Download and install <a href="http://www.videolan.org/vlc/index.html">VLC Media Player</a><br>
		<span class="number">2.</span> Right click the link and click "Copy link address". This may vary depending on your browser.<br>
		<span class="number">3.</span> Open VLC and click on "Media->Open Network Stream". This may vary between operating systems.<br>
		<span class="number">4.</span> Paste the URL in the input box and click play. The video will begin playing in a few seconds.<br>
		<span class="number">5.</span> After the video begins, click "Playback->Speed->Faster". Repeat until you get to the desired speed.<br>
	</div>
      </div>
      
      	<script>
		var docHeight = $(document).height();
		$(function() {
			
			function runEffect(hide) {			
				$("#speed-instructions").toggle("blind", {}, 500, function(hide) {
					console.log(hide);
					if (hide == true) {
					   	$('html').css('height', docHeight);
					}
					else {
						console.log($(document).height());
					   	setTimeout(function() {$('html').css('height', $(document).height() + 80)}, 500);
					}
				}(hide));
			};
			
			$("#speed-instructions").hide();
			$("#speed-instructions-toggle").click(function() {
				var img = $("#speed-instructions-toggle img");
				var hide;
				if (img.attr("src") == "images/right_arrow.png") {
					img.attr("src", "images/down_arrow.png");
					hide = false;
				} else {
					img.attr("src", "images/right_arrow.png");
					hide = true;
				} 
				runEffect(hide);
				return false;
			});
		});
		</script>
      
      <?php elseif ($invalid == true): ?>
      <div id="result">
	      <div id="invalid-message">
	        Looks like you entered an invalid URL, try again with a valid one.
	      </div>
	  </div>
      <?php endif; ?>
           <div id="counter"><input type="hidden" name="counter-value" /><span id="counter-text">Lectures Have Been Downloaded!</span></div>
		<script type="text/javascript">
		/* <![CDATA[ */
		        jQuery(document).ready(function($) {
		        	$("#counter").flipCounter({
		                number:<?php echo $count;?>, // the initial number the counter should display, overrides the hidden field
		                numIntegralDigits:1, // number of places left of the decimal point to maintain
		                numFractionalDigits:0, // number of places right of the decimal point to maintain
		                digitClass:"counter-digit", // class of the counter digits
		                counterFieldName:"counter-value", // name of the hidden field
		                digitHeight:40, // the height of each digit in the flipCounter-medium.png sprite image
		                digitWidth:30, // the width of each digit in the flipCounter-medium.png sprite image
		                imagePath:"images/flipCounter-medium.png", // the path to the sprite image relative to your html document
		                easing: false, // the easing function to apply to animations, you can override this with a jQuery.easing method
		                duration:1000, // duration of animations
		                onAnimationStarted:false, // call back for animation upon starting
		                onAnimationStopped:false, // call back for animation upon stopping
		                onAnimationPaused:false, // call back for animation upon pausing
		                onAnimationResumed:false // call back for animation upon resuming from pause
		        	});
		        });
		        function updateCounter () {
			        var response = $.ajax({
				        url:"getcount.php",
				        dataType:"json",
				        success:function(data) {
					        $("#counter").flipCounter(
				                "startAnimation",
				                {
					                end_number: data["count"] // the number we want the counter to scroll to
				                }
				        );},
			        });
		        }
		        setInterval(updateCounter, 5000); 
		/* ]]> */
		</script>
     </div><!-- #wrapper -->
     <div id="footer">
     	<form target="_blank" action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
			Running this site costs money, and that is something I don't have a lot of. If this site helped you, please <button id="donate-link" type="submit" name="submit">donate one dollar</button> to keep this site running.
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
       If you would like to help make this site not look like crap, report a bug, or contribute in any other way (jQuery, PHP, etc.), email me, <a target="_blank" href="http://www.gulshansingh.com">Gulshan Singh</a>, at <a target="_blank" href="mailto:gulshan@umich.edu">gulshan@umich.edu</a>
     </div>
  </body>
</html>
