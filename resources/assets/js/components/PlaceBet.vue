<template>
    <div class="place-bet" v-if="fetched && !hideCard">
        <h3 v-if="fetchedUser"> You have {{ user.user_info.credits }} credits to place your bet </h3>
        <div class="row">
            <div class="col-sm-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row" style="margin-bottom: 20px;">
                            <div class="col-md-4">
                                Question
                            </div>
                            <div class="col-md-4">
                                Answer
                            </div>
                            <div class="col-md-3">
                                Credits Placed
                            </div>
                            <div class="col-md-1">
                                Multiplier
                            </div>
                        </div>
                        <div class="row" v-for="question in betData.questions" style="padding-bottom: 20px;">
                            <div class="col-md-4">
                                {{ question.description }}
                            </div>
                            <div class="col-md-4">  <!-- v-html="formatAnswer(question)" -->           
                                <select v-if="question.type == 'team_id'" :selected="betData.teams['100'].name" v-model="question.answer">
                                    <option v-for="_team in betData.teams" v-bind:value="_team.match_team_id">
                                        {{ _team.name }}
                                    </option>
                                </select>      
                                <select v-if="question.type == 'champion_id'" :selected="betData.champions[0].champion_name" v-model="question.answer">
                                    <option v-for="_champ in betData.champions" v-bind:value="_champ.api_id">
                                        {{ _champ.champion_name }} {{ _champ.pick_scale }}
                                    </option>
                                </select>   
                                <select v-if="question.type == 'boolean'" :selected="true" v-model="question.answer">
                                    <option v-bind:value="1">True</option>
                                    <option v-bind:value="0">False</option>
                                </select>
                                <input v-if="question.type == 'integer'" type="text" name="question.question_id" v-model="question.answer">  
                                <input v-if="question.type == 'time_duration'" type="text" name="question.question_id" v-model="question.answer">
                                <span v-if="question.type == 'summoner_id_list'">
                                    <select :selected="betData.summmoners[0].summoner_name" multiple v-model="question.answer">
                                        <option v-for="_sums in betData.summmoners" v-bind:value="_sums.api_id">
                                            {{ _sums.summoner_name }}
                                        </option>
                                    </select>
                                </span>    
                                <span v-if="question.type == 'champion_id_list_5'">  
                                    <select :selected="betData.champions[0].champion_name" multiple v-model="question.answer">
                                        <option v-for="_champ in betData.champions" v-bind:value="_champ.api_id">
                                            {{ _champ.champion_name }}
                                        </option>
                                    </select>
                                </span>   
                                <span v-if="question.type == 'item_id_list'">
                                    <select :selected="betData.items[0].item_name" multiple v-model="question.answer">
                                        <option v-for="_item in betData.items" v-bind:value="_item.api_id">
                                            {{ _item.item_name }}
                                        </option>
                                    </select>  
                                </span>
                            </div>
                            <div class="col-md-3">
                                <input type="text" v-model="question.credits">
                            </div>
                            <div class="col-md-1">
                                {{ multiplier[question.question_id] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         <h3 style="display: inline"> You have {{ betData.reroll_remaining }} Reroll's left </h3>
        <button @click="submitAnswer" type="button" class="btn btn-primary pull-right">Submit Bet</button>
        <button v-if="betData.reroll_remaining > 0" @click="rerollCard" type="button" class="btn btn-warning pull-right">Reroll Card</button>
    </div>
</template>

<script>
export default {

    data() {
        return {
            betData: null,
            multiplier: null,
            user: null,
            fetched: false,
            fetchedUser: null,
            gameId: null,
            questionCount: null,
            reroll: null,
            hideCard: true,
        };
    },

    methods: {
        rerollCard: function() {
            this.getBetInfo(this.gameId, this.questionCount, true);
        },
        toggleCard: function(toggle) {
            this.hideCard = toggle;
        },
        getBetInfo: function(gameID, questionCount, reroll) {

            this.gameId = gameID;
            this.questionCount = questionCount;
            this.reroll = reroll;

            this.$http.post('/api/cards/create?api_game_id='+ gameID +'&question_count=' + questionCount + (reroll ? '&reroll=1' : '')).then(response => {
                this.betData = response.data;
                this.multiplier = {};
                for (var i = 0; i < this.betData.questions.length; i++) {
                    this.betData.questions[i]['answer'] = [];
                    this.betData.questions[i]['credits'] = 100;
                    this.betData.questions[i]['api_game_id'] = gameID;
                    this.multiplier[this.betData.questions[i].question_id] = this.betData.questions[i].multiplier;
                }
                this.fetched = true;
                this.hideCard = false;
                this.getUserInfo(this.betData.user_id);
            }).catch(function (error) {
                console.log(error);
            });
        },
        getUserInfo: function(userID) {
            this.$http.get('/api/profile?user_id=' + userID).then(response => {
                this.user = response.data;
                this.fetchedUser = true;
            }).catch(function (error) {
                console.log(error);
            });
        },
        submitAnswer: function() {

            var body = [];
            for (var i = 0; i < this.betData.questions.length; i++) {
                body.push({
                    api_game_id: this.betData.questions[i].api_game_id,
                    user_answer: this.flatten(this.betData.questions[i].answer),
                    credits_placed: this.betData.questions[i].credits,
                    question_slug: this.betData.questions[i].slug,
                });
            }



            // console.log(body);

            this.$http.post('/api/bets/create', {'bets': body, 'debug': true}).then(response => {
                if( response.status == 200) {
                    this.hideCard = true;
                    this.$parent.getGameBet(this.gameId);
                }
            }).catch(function (error) {
                console.error(error);
            });
        },
        flatten: function(answer) {
            if(Array.isArray(answer)) {
                return answer.map(function(val) {
                    return parseInt(val, 10);
                }).sort(function (a, b) {
                    return a - b;
                }).join(',');
            }

            return answer;
        }
    },
}
</script>
