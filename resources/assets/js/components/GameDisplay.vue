<template>
        <div class="row">
        	<div class="col-md-12 col-lg-8">
                <div class="embed-responsive embed-responsive-16by9">
        			<iframe
        			    src="http://player.twitch.tv/?channel=lolesportslas" 
                        class="embed-responsive-item"
        			    frameborder="0" 
        			    scrolling="no"
        			    allowfullscreen="true">
        			</iframe>
                </div>
    		</div>
    		<div class="hidden-md hidden-sm hidden-xs col-md-4 col-lg-4">
        			<iframe frameborder="0" 
        		        scrolling="no" 
        		        id="chat_embed" 
                        height="500"
        		        src="http://www.twitch.tv/lolesportslas/chat">
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
