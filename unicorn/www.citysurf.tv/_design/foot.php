</div>	<!-- end contentContainer -->

<div id="foot" style="clear: both">
	<p class="no l">CitySurf.tv Â© 2007. All rights reserved.</p>
	<a href="text_big.php?v=register-accept" class="down">villkor</a> | <a href="text_big.php?v=cookies" class="down">cookies</a><br/>
	<? if ($user->isAdmin) @$db->showProfile($time_start); ?>
</div>

</body>
</html>
