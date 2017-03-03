<template>
        <div class="row">
    	<div class="col-md-8">
    	<!-- change channels to dynamic when done scraping from lolesports -->
			<iframe
			    src="http://player.twitch.tv/?channel=lolesportslas" 
			    height="480" 
			    width="	720" 
			    frameborder="0" 
			    scrolling="no"
			    allowfullscreen="true"
			    style="max-width: 80em;">
			</iframe>
		</div>
		<div class="col-md-4">
			<iframe frameborder="0" 
		        scrolling="no" 
		        id="chat_embed" 
		        src="http://www.twitch.tv/lolesportslas/chat" 
		        height="480" 
		        width="300">
			</iframe>
		</div>
	</div>
</template>

<script>
export default {
    props: ['match'],

    mounted() {

    },

    data() {
        return {
            shared: store.state,
        };
    },

    methods: {

    },
}
</script>