<template>
    <div class="place-bet">
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
        <div class="row" v-if="fetched == true" v-for="question in betData.questions" style="padding-bottom: 20px;">
            <div class="col-md-4">
                {{ question.description }}
            </div>
            <div class="col-md-4" v-html="formatAnswer(question)">
            </div>
            <div class="col-md-3">
                <input type="text" name="fname">
            </div>
            <div class="col-md-1">
                8
            </div>
        </div>


    <div id="vue">
      <select v-model="choice" options="list"></select>
    </div>

    </div>
</template>

<script>
export default {
    props: [],

    mounted() {
        this.getBetInfo('106ba94f-1be5-42e1-8f0b-c7c52fba0930', 3, 1);
    },

    data() {
        return {
            gameID: null,
            questionCount: null,
            betData: null,
            fetched: false,
        };
    },

    methods: {
        getBetInfo: function(gameID, questionCount, reroll) {
            this.$http.post('/api/cards/create?api_game_id='+ gameID +'&question_count=' + questionCount + (reroll ? '&reroll=1' : '')).then(response => {
                this.betData = response.data;
                this.fetched = true;
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



new Vue({
    el: '#vue',
    data: {
        choice: 2,
        list: [
              { text: 'Item 1', value: 1 },
              { text: 'Item 2', value: 2 },
              { text: 'Item 3', value: 3 }
        ]
    }
});
</script>