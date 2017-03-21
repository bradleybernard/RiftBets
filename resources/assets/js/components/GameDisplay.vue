<template>
    <div class="game-display">
        <div class="game-header" v-if="matchFetched == true">
            <div class="row text-center" style="font-size: 25px; line-height: 75px;">
                <div class="col-md-12 col-lg-8">
                    <div class="col-xs-4 ">
                        <img :src="matchData.resources.one.logo_url" style="width: 75px; height: 75px">
                    </div>
                    <div class="col-xs-4" style="line-height: 30px;">
                        {{ matchData.name }} <br/>
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
                        <button 
                            v-for="gameNum in bestOf"
                            :class="[currentGame.number == gameNum ? 'active' : '']" 
                            @click="changeGame(gameNum)" 
                            :disabled="gameNum > (matchData.score_one + matchData.score_two) + 10"
                            type="button" 
                            class="btn btn-default"
                        >Game {{ gameNum }}</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="game-video" v-if="matchFetched == true" style="padding-top: 1em;">
            <div class="row">
                <div class="col-md-12 col-lg-8">
                    <div class="embed-responsive embed-responsive-16by9">
                        <iframe
                            v-if="matchData.state == 'resolved'"
                            :src="currentGame.video" 
                            class="embed-responsive-item"
                            frameborder="0" 
                            scrolling="no"
                            allowfullscreen="true">
                        </iframe>
                        <iframe
                            v-else
                            src="http://player.twitch.tv/?channel=nalcs1" 
                            class="embed-responsive-item"
                            frameborder="0" 
                            scrolling="no"
                            allowfullscreen="true">
                        </iframe>
                    </div>
                </div>
                <div class="hidden-sm hidden-xs col-md-4 col-lg-4">
                    <iframe v-if="matchData.state !='resolved'"
                        frameborder="0"
                        scrolling="no" 
                        id="chat_embed" 
                        height="500"
                        src="http://www.twitch.tv/nalcs1/chat">
                    </iframe>
                    <div v-else>
                        <div v-for="team in ['team_one', 'team_two']">
                            <div v-for="player in playerStats(team)" class="row">
                                <div class="col-sm-12">
                                    <div class="row" style="padding-top: 5px">
                                        <div class="col-md-1">
                                            <img style="width: 30px; height: 30px;" :src="player.champion.image_url">
                                        </div>
                                        <div class="col-md-8">
                                            {{ player.summoner_name }}
                                        </div>
                                        <div class="col-md-3" style="padding: 0">
                                            {{ player.minions_killed }}
                                            {{ player.gold_earned }}<img style="width: 15px; height: 15px" src="https://files.stage.gg/statIcons/scoreboardicon_gold.png">
                                            
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-9">
                                            <img style="width: 25px; height: 25px;" v-if="player['item_0']" :src="player['item_0']['image_url']">
                                            <img style="width: 25px; height: 25px;" v-if="player['item_1']" :src="player['item_1']['image_url']">
                                            <img style="width: 25px; height: 25px;" v-if="player['item_2']" :src="player['item_2']['image_url']">
                                            <img style="width: 25px; height: 25px;" v-if="player['item_3']" :src="player['item_3']['image_url']">
                                            <img style="width: 25px; height: 25px;" v-if="player['item_4']" :src="player['item_4']['image_url']">
                                            <img style="width: 25px; height: 25px;" v-if="player['item_5']" :src="player['item_5']['image_url']">
                                            <img style="width: 25px; height: 25px;" v-if="player['item_6']" :src="player['item_6']['image_url']">
                                        </div>
                                        <div class="col-sm-3" style="padding: 0">
                                            {{ player.kills }} / {{ player.deaths }} / {{ player.assists }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="game-bets" v-if="betFetched == true" style= "color: #2b2b2d;">
            <div class="row" v-if="betData.length > 0">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Bet Card ({{ betData[0].is_complete ? 'Complete' : 'Pending'}})</h3>
                        </div>
                        <div class="panel-body text-center">
                            <div class="row" style="margin-bottom: 20px;">
                                <div class="col-lg-12">
                                    <div class="col-md-6"> 
                                        Question
                                    </div>
                                    <div class="col-md-2">
                                        User
                                    </div>
                                    <div class="col-md-2">
                                        Answer
                                    </div>
                                    <div class="col-md-2">
                                        Result
                                    </div>
                                </div>
                            </div>
                            <div class="row" v-for="bet in betData" style="padding-bottom: 20px;">
                                <div class="col-md-6">
                                    {{ bet.description }}
                                </div>
                                <div class="col-md-2" v-html="formatAnswer(bet, true) ">
                                </div>
                                <div class="col-md-2" v-html="formatAnswer(bet, false)">
                                </div>
                                <div class="col-md-2" v-if='betData[0].is_complete'>
                                    Placed: {{ bet.credits_placed }} <br/>
                                    Outcome: {{ bet.won ? "Won" : "Lost" }} <br/>
                                    Result: {{ bet.won ? "+" + bet.credits_won : "-" + bet.credits_placed }}
                                </div>
                                <div class="col-md-2" v-else>
                                    Placed: {{ bet.credits_placed }} <br/>
                                    Outcome: TBD
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <place-bet></place-bet>
        <div>
            <h3> Recent Matches </h3>
            <div class="row" v-if="matchFetched == true">
                <div class="col-xs-6">
                    <past-matches v-bind:api_res_id="matchData.api_resource_id_one"></past-matches>
                </div>
                <div class="col-xs-6">
                    <past-matches v-bind:api_res_id="matchData.api_resource_id_two"></past-matches>
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
        this.listenForMatch(this.match);
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
        listenForMatch: function(id) {
            Echo.channel('match.' + id)
                .listen('GameCompleted', (e) => {
                    if(this.shared.user.loggedIn) {
                        this.getGameBet(this.currentGame.apiGameId);
                    }
                });
        },
        getMatchData: function(id) {
            this.$http.get('/api/match?match_id=' + id).then(response => {
                this.matchData = response.data;
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

            if(matchData.state == "resolved") {
                gameNum = 1;
            }

            this.changeGame(gameNum);
        },
        changeGame: function (gameNum) {

            this.betFetched = false;
            gameNum = (gameNum == 0 ? 1 : gameNum);

            var gameKey = "G" + gameNum;
            for(var game of this.matchData.game_api_ids) {
                if(game.name == gameKey) {
                    this.currentGame = {
                        number: gameNum,
                        name: game.name,
                        apiGameId: game.api_game_id,
                        video: this.gameVideo(gameNum),
                    };
                    break;
                }
            }

            this.matchFetched = true;
            this.$children[0].getBetInfo(this.currentGame.apiGameId, 5, 0);

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
        formatAnswer: function(bet, userAnswer) {

            let value = (userAnswer ? bet.user_answer : bet.answer);
            if(value == null) {
                return null;
            }

            if(bet.type == "time_duration") {
                let minutes = Math.floor(value / 60);
                let seconds = value - minutes * 60;

                if (minutes < 10) {minutes = "0"+minutes;}
                if (seconds < 10) {seconds = "0"+seconds;}

                return minutes + ":" + seconds;
            }

            if(bet.type == "team_id") {
                return "<img style='width: 50px; height: 50px;' src='" + value.logo_url + "'>";
            }

            if(bet.type == "integer") {
                return value;
            }

            if(bet.type == 'boolean') {
                return (value == 1 ? "True" : "False");
            }

            if(bet.type == 'champion_id') {
                return value.champion_name;
            }

            if(bet.type == 'champion_id_list_5') {
                var ret = "";
                for (const champ of value) {
                    ret += champ.champion_name + " ";
                }
                return ret;
            }

            if(bet.type == 'item_id_list') {
                var ret = "";
                for (const item of value) {
                    ret += item + " ";
                }
                return ret;
            }

            if(bet.type == 'summoner_id_list') {
                var ret = "";
                for (const item of value) {
                    ret += item.name + " ";
                }
                return ret;
            }
        },
        gameVideo: function (gameNum) {
            var path = null;

            if(gameNum == 1 && this.matchData.state == 'unresolved') {
                return null;
            } else if(gameNum == 1) {
                path = this.matchData.game_one;
            } else if(gameNum == 2) {
                path = this.matchData.game_two;
            } else if(gameNum == 3) {
                path = this.matchData.game_three;
            } else if(gameNum == 4) {
                path = this.matchData.game_four;
            } else if(gameNum == 5) {
                path = this.matchData.game_five;
            }

            if(path == null) {
                return null;
            }

            return (path.videos ? path.videos[0].source : null);
        },
        playerStats: function(team) {
            let dict = {
                1: "one",
                2: 'two',
                3: 'three',
                4: 'four',
                5: 'five',
            };

            var key = 'game_' + dict[this.currentGame.number];
            return this.matchData[key][team].player_stats;
        },
    },
}
</script>
