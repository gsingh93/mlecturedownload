<?php
$mysqli = new mysqli("localhost", "root", "root", "mlecturedownload");
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
		<input type="submit" value="Download!" id="submit">
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
      		echo "<a id='video-url' target=\"_blank\" href=\"$video_path\">Watch Video</a><br>";
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
       If you would like to make this site not look like crap or if you would like to report a bug, email me, Gulshan Singh, at gulshan@umich.edu
     </div>
  </body>
</html>
