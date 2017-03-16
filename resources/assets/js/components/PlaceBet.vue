<template>
    <div class="place-bet" v-if="fetched == true">
        <div v-if="fetchedUser == true"> 
            <h1> Hi {{user.user_info.name }} </h1>
            <h3> You have {{ user.user_info.credits }} credits to place your bet </h3>
            </br>
            </br>
            </br>
        </div>
        <div class="row text-center" style="font-size: 25px; line-height: 75px;">
            <div class="col-xs-12">
                <div class="col-xs-4 ">
                    <img :src="betData.teams['100'].logo_url" style="width: 75px; height: 75px">
                </div>
                <div class="col-xs-4" style="line-height: 30px;">
                    {{ betData.teams['100'].name }} 
                    vs
                    {{ betData.teams['200'].name }} 
                </div>
                <div class="col-xs-4">
                    <img :src="betData.teams['200'].logo_url" style="width: 75px; height: 75px">
                </div>
            </div>
            </br>
            </br>
        </div>
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
                <select v-if="question.type == 'team_id'" :selected="betData.teams['100'].name">
                    <option v-for="_team in betData.teams" v-bind:value="_team.id">
                        {{ _team.name }}
                    </option>
                </select>      
                <select v-if="question.type == 'champion_id'" :selected="betData.champions[0].champion_name">
                    <option v-for="_champ in betData.champions" v-bind:value="_champ.api_id">
                        {{ _champ.champion_name }}
                    </option>
                </select>   
                <select v-if="question.type == 'boolean'" :selected="true">
                    <option v-bind:value="1">True</option>
                    <option v-bind:value="0">False</option>
                </select>
                <input v-if="question.type == 'integer'" type="text" name="question.question_id">  
                <input v-if="question.type == 'time_duration'" type="text" name="question.question_id">                  
                <span v-if="question.type == 'summoner_id_list'">
                    <select :selected="betData.summmoners[0].summoner_name">
                        <option v-for="_sums in betData.summmoners" v-bind:value="_sums.api_id">
                            {{ _sums.summoner_name }}
                        </option>
                    </select>  
                    <select :selected="betData.summmoners[0].summoner_name">
                        <option v-for="_sums in betData.summmoners" v-bind:value="_sums.api_id">
                            {{ _sums.summoner_name }}
                        </option>
                    </select> 
                </span>    
                <span v-if="question.type == 'champion_id_list_5'">  
                    <select :selected="betData.champions[0].champion_name">
                        <option v-for="_champ in betData.champions" v-bind:value="_champ.api_id">
                            {{ _champ.champion_name }}
                        </option>
                    </select> 
                    <select :selected="betData.champions[0].champion_name">
                        <option v-for="_champ in betData.champions" v-bind:value="_champ.api_id">
                            {{ _champ.champion_name }}
                        </option>
                    </select> 
                    <select :selected="betData.champions[0].champion_name">
                        <option v-for="_champ in betData.champions" v-bind:value="_champ.api_id">
                            {{ _champ.champion_name }}
                        </option>
                    </select> 
                    <select :selected="betData.champions[0].champion_name">
                        <option v-for="_champ in betData.champions" v-bind:value="_champ.api_id">
                            {{ _champ.champion_name }}
                        </option>
                    </select> 
                    <select :selected="betData.champions[0].champion_name">
                        <option v-for="_champ in betData.champions" v-bind:value="_champ.api_id">
                            {{ _champ.champion_name }}
                        </option>
                    </select> 
                </span>   
                <span v-if="question.type == 'item_id_list'">
                    <select :selected="betData.items[0].item_name">
                        <option v-for="_item in betData.items" v-bind:value="_item.api_id">
                            {{ _item.item_name }}
                        </option>
                    </select>  
                    <select :selected="betData.items[0].item_name">
                        <option v-for="_item in betData.items" v-bind:value="_item.api_id">
                            {{ _item.item_name }}
                        </option>
                    </select>  
                    <select :selected="betData.items[0].item_name">
                        <option v-for="_item in betData.items" v-bind:value="_item.api_id">
                            {{ _item.item_name }}
                        </option>
                    </select>  
                    <select :selected="betData.items[0].item_name">
                        <option v-for="_item in betData.items" v-bind:value="_item.api_id">
                            {{ _item.item_name }}
                        </option>
                    </select>  
                    <select :selected="betData.items[0].item_name">
                        <option v-for="_item in betData.items" v-bind:value="_item.api_id">
                            {{ _item.item_name }}
                        </option>
                    </select>  
                    <select :selected="betData.items[0].item_name">
                        <option v-for="_item in betData.items" v-bind:value="_item.api_id">
                            {{ _item.item_name }}
                        </option>
                    </select>  
                </span>
            </div>
            <div class="col-md-3">
                <input type="text" name="fname">
            </div>
            <div class="col-md-1">
                {{ question.multiplier }}
                <!-- {{ multiplier[question.question_id] }} -->
            </div>
        </div>
        <button type="button" class="btn btn-default pull-right">Submit Bet</button>
    </div>
</template>

<script>
export default {
    props: [],

    mounted() {
        this.getBetInfo('106ba94f-1be5-42e1-8f0b-c7c52fba0930', 3, 1);
        this.getUserInfo(1);
    },

    data() {
        return {
            gameID: null,
            questionCount: null,
            betData: null,
            fetched: false,
            multiplier: null,
            user: null,
            fetchedUser: null,
        };
    },

    methods: {
        getBetInfo: function(gameID, questionCount, reroll) {
            this.$http.post('/api/cards/create?api_game_id='+ gameID +'&question_count=' + questionCount + (reroll ? '&reroll=1' : '')).then(response => {
                this.betData = response.data;
                var question;
                var array = {};
                for (question in this.betData.questions){
                    var index = String(question.question_id);
                    array[index] = question.multiplier;
                }
                this.multiplier = array;
                this.fetched = true;
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

    formatAnswer: function(question) {

            var value = 100;

            if(question.type == "team_id") {
                return "<img style='width: 50px; height: 50px;' src='" + this.betData.teams['100'].logo_url + "'><img style='width: 50px; height: 50px;' src='" + this.betData.teams['200'].logo_url + "'>";
            }

            if(question.type == "integer") {
                return value;
            }

            return value;
        },
    },
}
</script>