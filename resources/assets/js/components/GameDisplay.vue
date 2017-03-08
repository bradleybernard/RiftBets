<template>
<div>
    <div class="game-nav">
        <div class="row" v-if="fetched == true" style="font-size: 25px; line-height: 75px;" >
            <div class="col-xs-4 ">
                <img :src="matchData.resources.one.logo_url" style="width: 75px; height: 75px">
            </div>
            <div class="col-xs-4">
                <span class="hidden-xs">{{ matchData.name }}</span>
                {{ matchData.score_one }} - {{ matchData.score_two }}
            </div>
            <div class="col-xs-4">
                <img :src="matchData.resources.two.logo_url" style="width: 75px; height: 75px">
            </div>
        </div>
    </div>
    <div class="game-display" style="padding-top: 1em;">
        <div class="row">
            <div class="col-md-12 col-lg-8">
                <div class="embed-responsive embed-responsive-16by9">
                    <iframe v-if="matchData.hasOwnProperty('game_one')"
                        :src="matchData.game_one.videos[0].source" 
                        class="embed-responsive-item"
                        frameborder="0" 
                        scrolling="no"
                        allowfullscreen="true">
                    </iframe>
                    <iframe v-else
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
    </div>
</div>
</template>

<script>
export default {
    props: ['match'],

    mounted() {
        this.getMatchData(this.match);
    },

    data() {
        return {
            shared: store.state,
            matchData: [],
            fetched : false,
        };
    },

    methods: {
        getMatchData: function(id) {
            this.stats = this.$http.get('http://riftbets.dev/api/match?match_id=' + id).then(response => {
                this.matchData = response.data;
                this.fetched = true;
            }).catch(function (error) {
                console.log(error);
            });
        },
    },
}
</script>