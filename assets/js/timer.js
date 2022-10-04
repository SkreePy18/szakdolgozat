// global variables

var remaining_time = 0; // countdown duration
var msg_endtime = ''; // message to display at the end of time
var start_time = 0; // client computer datetime in milliseconds
var displayendtime = true; // display popup message indicating the end of the time
var logout_page = ''; // logout page

/**
 * Display current server date-time and remaining time (countdown)
 */
function topic_timer() {
		// get local time
		var today = new Date();
		// elapsed time in seconds
		var diff_seconds = remaining_time + ((today.getTime() - start_time) / 1000);
		//get sign
		var sign = '-';
		if (diff_seconds >= 0) {
			sign = '+';
			if (displayendtime && (msg_endtime.length > 1)) {
				displayendtime = false;
				alert(msg_endtime);
  			window.location.replace(logout_page);
			}
		}
    if(diff_seconds < -60) {
      document.getElementById('mytimer').style.color = "#888888";
    } else {
      document.getElementById('mytimer').style.color = "red";
    }
		diff_seconds = Math.abs(diff_seconds); // get absolute value
		// split seconds in HH:mm:ss
		var diff_hours = Math.floor(diff_seconds / 3600);
		diff_seconds  = diff_seconds % 3600;
		var diff_minutes = Math.floor(diff_seconds / 60);
		diff_seconds  = Math.floor(diff_seconds % 60);
		if(diff_hours < 10) {
			diff_hours = "0" + diff_hours;
		}
		if(diff_minutes < 10) {
			diff_minutes = "0" + diff_minutes;
		}
		if(diff_seconds < 10) {
			diff_seconds = "0" + diff_seconds;
		}
		// display countdown string on form field
    document.getElementById('mytimer').innerText = ''+sign+''+diff_hours+':'+diff_minutes+':'+diff_seconds+' ';
	return;
}

/**
 * Starts the timer
 * @param int remaining remaining time in seconds
 * @param string msg  message to display at the end of countdown
 * @param string msg  page to load when time expires
 */
function topic_start_timer(remaining, msg, logout) {
	var startdate = new Date();
	start_time = startdate.getTime();
	remaining_time = remaining;
	msg_endtime = msg;
	logout_page = logout;
	// update clock
	setInterval('topic_timer()', 500);
}

// --------------------------------------------------------------------------
//  END OF SCRIPT
// --------------------------------------------------------------------------
