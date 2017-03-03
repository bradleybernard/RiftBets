<template>

<div>
    <select v-model="selected" selected="all">
    <option v-for="league in leagues" v-bind:value="league.value">
    {{ league.text }}
    </option>
    </select>
    <span>Selected: {{ selected }}</span>

    <div v-if="fetched == true">
        <div v-for="(item, key) in stats">
            <h1>
            <!-- string.charAt(0).toUpperCase() + string.slice(1); -->
            {{ key }}: {{ item[0].block_prefix.charAt(0).toUpperCase() + item[0].block_prefix.slice(1) }} {{ item[0].block_label }} {{ item[0].sub_block_prefix }} {{ item[0].sub_block_label }}
            </h1>
            <div v-for="match in item" v-if="match.league_id == selected || selected == 'all'" style="color: white; padding-top: 1em">
                <div style="width:81.5em; height:11em; border:1px solid #000;  vertical-align: middle;">
                    <h1 class="row" style="padding-left: 1em; display: inline-block;">
                        <div class="col-md-1" style="padding-top: 1em">
                            <span class="label label-default">{{ match.scheduled_time.substring(10,16) }}</span>
                        </div>
                        <div class="col-md-2 col-md-offset-2">
                            <img :src="match.resources.one.logo_url" style="max-width: 70%;"></img>
                        </div>
                        <div class="col-md-3" style="padding-top: 1em">
                            <a :href="matchLink(match.api_id_long)">
                            <span style="">{{ match.name }}</span>
                            </a>
                        </div>
                        <div class="col-md-2">
                            <img :src="match.resources.two.logo_url" style="max-width: 70%"></img>
                        </div>
                    </h1>
                </div>
            </div>
            <hr>
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
            stats: [],
            shownStats: [],
            fetched: false,
            leagues: [
                {text: 'All', value: 'all'},
                {text: 'NA-LCS', value: 'na-lcs'},
                {text: 'EU-LCS', value: 'eu-lcs'},
                {text: 'LCK', value: 'lck'}
            ],
            selected: 'all'
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
