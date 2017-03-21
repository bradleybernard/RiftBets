<template>
    <div class="past-matches panel panel-default" v-if="fetched == true" style="margin-bottom: 20px; margin-top: 20px; padding-top: 20px;">
        <div class="row text-center" style="margin-bottom: 20px;"> 
            <h3> {{ matches.team[0].name }}</h3>
        </div>
        <div class="row" v-for="match in matches.matches" style="padding-bottom: 20px;">
            <div class="col-md-4">
                <img class="center-block" style="width: 50px; height: 50px;" :src="match.team_one_logo">
            </div>
            <div class="col-md-4 text-center">
                <h4>{{ match.score_one }} - {{ match.score_two }}</h4>
                <span>{{ match.scheduled_time }}</span>
            </div>
            <div class="col-md-4">
                <img class="center-block" style="width: 50px; height: 50px;" :src="match.team_two_logo">
            </div>
        </div>
    </div>
</template>

<script>
export default {

    props: ['api_res_id'],

    data() {
        return {
            matches: null,
            fetched: false,
        };
    },

    mounted() {
        this.getPastMatches(this.api_res_id);
    },

    methods: {

        getPastMatches: function(resID) {

            this.$http.get('/api/schedule/past?resID='+ resID).then(response => {
                this.matches = response.data;
                this.fetched = true;
            }).catch(function (error) {
                console.log(error);
            });
        },
    },
}
</script>
