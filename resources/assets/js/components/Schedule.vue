<template>
    <div class="schedule">
        <div class="row">
            <div class="col-lg-12">
                <span>League: </span>
                <select v-model="league" selected="all">
                    <option v-for="_league in leagues" v-bind:value="_league.value">
                        {{ _league.text }}
                    </option>
                </select>
                <span>Week: </span>
                <select v-model="week" selected="1">
                    <option v-for="_week in weeks" v-bind:value="_week.value">
                        {{ _week.text }}
                    </option>
                </select>
<!--                 <label v-for="_week in weeks"> 
                    <input type="radio" v-model="week" v-bind:value="_week.value"> 
                    {{ _week.text }}
                </label> -->
            </div>
        </div>

        <div class="date-group" v-if="fetched == true" v-for="(item, key) in stats">
            <div class="row">
                <div class="col-lg-12">
                    <h1>
                        {{ key }}<!-- : {{ item[0].block_prefix.charAt(0).toUpperCase() + item[0].block_prefix.slice(1) }} {{ item[0].block_label }} {{ item[0].sub_block_prefix }} {{ item[0].sub_block_label }} -->
                    </h1>
                </div>
            </div>
            <div class="row" v-if="Number(match.block_label) == week && (match.league_id == league || league == 'all')"  v-for="match in item" style="color: white; font-size: 25px; line-height: 75px;">
                <div class="col-md-2">
                    <span class="label label-default">{{ match.scheduled_time.substring(10,16) }}</span>
                </div>
                <div class="col-md-3">
                    <img :src="match.resources.one.logo_url" style="width: 75px; height: 75px;"></img>
                </div>
                <div class="col-md-4">
                    <a :href="matchLink(match.api_id_long)">{{ match.name }}</a>
                </div>
                <div class="col-md-3">
                    <img :src="match.resources.two.logo_url" style="width: 75px; height: 75px;"></img>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    mounted() {
        this.getSchedule();
    },

    data() {
        return {
            shared: store.state,
            fetched: false,
            stats: [],
            shownStats: [],
            league: 'all',
            leagues: [
                {text: 'All', value: 'all'},
                {text: 'NA-LCS', value: 'na-lcs'},
                {text: 'EU-LCS', value: 'eu-lcs'},
                {text: 'LCK', value: 'lck'}
            ],
            week: 1,
            weeks: [
                {text: 1, value: 1},
                {text: 2, value: 2},
                {text: 3, value: 3},
                {text: 4, value: 4},
                {text: 5, value: 5},
                {text: 6, value: 6},
                {text: 7, value: 7},
                {text: 8, value: 8},
                {text: 9, value: 9},
                {text: 10, value: 10}
            ],
        };
    },

    computed: {

    },

    methods: {
        getSchedule: function() {
            this.stats = this.$http.get('http://riftbets.dev/api/schedule?league=')
              .then(response => {
                this.stats = response.data;
                this.fetched = true;
            }).catch(function (error) {
                console.log(error);
            });
        },
        matchLink: function(matchId) {
            return 'match/' + matchId;
        }
    },
}
</script>
