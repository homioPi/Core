<?php 
	if(!\HomioPi\Users\CurrentUser::signOut()) {
		\HomioPi\Response\error('error_signing_out', 'Failed to sign out.');
	}
?>
<script>
	HomioPi.page.load('login/main', true);
</script>