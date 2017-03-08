<template>
    <div class="game-display">
        <div class="game-header" v-if="matchFetched == true">
            <div class="row text-center" style="font-size: 25px; line-height: 75px;">
                <div class="col-md-12 col-lg-8">
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
            <div class="row">
                <div class="col-md-12 col-lg-8 text-center">
                    <div class="btn-group" role="toolbar">
                        <button v-for="gameNum in bestOf" type="button" @click="changeGame(gameNum)" v-bind:class="[currentGame.number == gameNum ? 'active' : '']" class="btn btn-default">Game {{ gameNum }}</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="game-video" v-if="matchFetched == true" style="padding-top: 1em;">
            <div class="row">
                <div class="col-md-12 col-lg-8">
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe
                            :src="matchData.game_one.videos[0].source" 
                            class="embed-responsive-item"
                            frameborder="0" 
                            scrolling="no"
                            allowfullscreen="true">
                        </iframe>
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
        </div>

        <div class="game-bets" v-if="betFetched == true">
            <div class="row">
                <div class="col-lg-12">
                    <div class="well">
                        <h4>Bets</h4>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    props: ['match', 'bestOf'],

    mounted() {
        this.getMatchData(this.match);
    },

    data() {
        return {
            shared: store.state,
            matchData: null,
            betData: null,
            currentGame: null,
            matchFetched: false,
            betFetched: false,
        };
    },

    methods: {
        getMatchData: function(id) {
            this.$http.get('/api/match?match_id=' + id).then(response => {
                console.log(response.data);
                this.matchData = response.data;
                this.matchFetched = true;
                this.setCurrentGame(response.data);
            }).catch(function (error) {
                console.log(error);
            });
        },

        setCurrentGame: function(matchData) {
            var games = ['game_one', 'game_two', 'game_three', 'game_four', 'game_five'];
            var gameNum = 1;

            for(const [key, game] of games.entries()) {
                if(!matchData.hasOwnProperty(game)) {
                    gameNum = key;
                    break;
                }
            }

            this.changeGame(gameNum);
        },

        changeGame: function (gameNum) {
            gameNum = (gameNum == 0 ? 1 : gameNum);

            var gameKey = "G" + gameNum;
            for(var game of this.matchData.game_api_ids) {
                if(game.name == gameKey) {
                    this.currentGame = {
                        number: gameNum,
                        name: game.name,
                        apiGameId: game.api_game_id,
                    };
                    break;
                }
            }

            if(this.shared.user.loggedIn) {
                this.getGameBet(this.currentGame.apiGameId);
            }
        },

        getGameBet: function (gameId) {
            this.$http.get('/api/bets/gamebet?api_game_id=' + gameId).then(response => {
                this.betData = response.data;
                this.betFetched = true;
            }).catch(function (error) {
                console.log(error);
            });
        },
    },
}
</script>
