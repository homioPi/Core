<?php 
	if(!\HomioPi\Users\CurrentUser::signOut()) {
		\HomioPi\Response\error('error_signing_out', 'Failed to sign out.');
	}
?>
<script>
	homioPi.page.load('login/main', true);
</script>