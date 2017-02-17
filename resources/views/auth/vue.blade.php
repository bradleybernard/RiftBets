<script>
var user = JSON.parse('{!! $payload !!}');
window.opener.setState(user);
window.close();
</script>
