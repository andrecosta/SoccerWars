Vue.component('match-details', {
    template: '#match-details',

    data: function() {
        return {
            loaded: false,
            match: null,
            bet: {
                sliders: null,
                advanced_total: 0
            },
            comments: null
        }
    },

    ready: function(){
        var self = this;
        app.setTitle("Match details");

        // Fetch data every second
        var timer = setInterval(function() {
            $.get(API_URL + '/matches/' + self.route.params.id)
                .done(function (response) {
                    self.loaded = true;
                    self.match = response;

                    // Fetch comments
                    $.get(API_URL + '/matches/' + self.route.params.id + '/comments')
                        .done(function (response) {
                            self.comments = response;
                        });
                })
                .fail(function (response) {
                    humane.log(response.responseJSON['error']);
                });

            // Setup bet sliders
            if (self.loaded && !self.bet.sliders && app.loaded) {
                self.bet.points_available = app.user.points;
                self.bet.sliders = {
                    simple: document.getElementById("bet_simple"),
                    advanced_goals: document.getElementById("bet_advanced_goals"),
                    advanced_yellowcards: document.getElementById("bet_advanced_yellowcards"),
                    advanced_redcards: document.getElementById("bet_advanced_redcards"),
                    advanced_defenses: document.getElementById("bet_advanced_defenses")
                };
                setupSliders(self.bet);
            }
        }, 1000, true);
        this.$add('timer', timer);
    },

    methods: {
        'bet_simple': function() {
            var total = parseInt(this.bet.sliders.simple.noUiSlider.get());
            var outcome = $("input[name=bet_simple_result]:checked").val();

            if (total > 0 && outcome != undefined) {
                $.ajax(API_URL + '/matches/' + this.match.id + '/bet', {
                    type: 'POST',
                    data: JSON.stringify({
                        type: 1,
                        team: outcome,
                        points_simple: total
                    })
                })
                    .done(function () {
                        $(".page-match-details .bets").remove();
                        if (total > app.user.points) total = app.user.points;
                        app.user.points -= total;
                    })
                    .fail(function (response) {
                        humane.log(response.responseJSON['error']);
                    });
            }
        },

        'bet_advanced': function() {
            var points_goals = parseInt(this.bet.sliders.advanced_goals.noUiSlider.get());
            var points_yellowcards = parseInt(this.bet.sliders.advanced_yellowcards.noUiSlider.get());
            var points_redcards = parseInt(this.bet.sliders.advanced_redcards.noUiSlider.get());
            var points_defenses = parseInt(this.bet.sliders.advanced_defenses.noUiSlider.get());
            var total = points_goals + points_yellowcards + points_redcards + points_defenses;
            var outcome = $("input[name=bet_advanced_team]:checked").val();

            if (total > 0 && outcome != undefined) {
                $.ajax(API_URL + '/matches/' + this.match.id + '/bet', {
                    type: 'POST',
                    data: JSON.stringify({
                        type: 2,
                        team: outcome,
                        points_goals: points_goals,
                        points_yellowcards: points_yellowcards,
                        points_redcards: points_redcards,
                        points_defenses: points_defenses
                    })
                })
                    .done(function () {
                        $(".page-match-details .bets").remove();
                        if (total > app.user.points) total = app.user.points;
                        app.user.points -= total;
                    })
                    .fail(function (response) {
                        humane.log(response.responseJSON['error']);
                    });
            }
        },

        'submit_comment': function(e) {
            if ($.trim(e.target.value)) {
                var message = e.target.value;
                $.ajax(API_URL + '/matches/' + this.match.id + '/comments', {
                    type: 'POST',
                    data: JSON.stringify({
                        text: message
                    })
                })
                    .done(function(){
                        e.target.value = '';
                    })
                    .fail(function(response){
                        humane.log(response.responseJSON['error']);
                    });
            }
        },

        // Check if the comment was made by the authenticate user and give it a different class
        'is_my_comment': function(user_id) {
            if (user_id == app.user.id)
                return 'mine';
        },

        // Check if the match ended
        'is_started': function() {
            return moment().isAfter(this.match.start_time);
        },

        // Check if the match is in progress
        'is_inprogress': function() {
            return moment().isBetween(this.match.start_time, this.match.end_time);
        },

        // Check if the match ended
        'is_ended': function() {
            return moment().isAfter(this.match.end_time);
        },

        // Check if a bet was already placed
        'bet_placed': function() {
            var match = this.match;
            var placed = false;
            if (app.user.bets) {
                $.each(app.user.bets, function () {
                    if (this.match_id == match.id) placed = true;
                });
            }
            return placed;
        }
    },

    beforeDestroy: function() {
        clearInterval(this.$get('timer'));
    }
});

function setupSliders(bet) {
    noUiSlider.create(bet.sliders.simple, {
        start: [0],
        step: 10,
        connect: 'lower',
        range: {
            'min': [0],
            'max': [parseInt(bet.points_available)]
        },
        pips: {
            mode: 'range',
            density: 3
        }
    });
    noUiSlider.create(bet.sliders.advanced_goals, {
        start: [0],
        step: 10,
        connect: 'lower',
        range: {
            'min': [0],
            'max': [parseInt(bet.points_available)]
        }
    });
    noUiSlider.create(bet.sliders.advanced_yellowcards, {
        start: [0],
        step: 10,
        connect: 'lower',
        range: {
            'min': [0],
            'max': [parseInt(bet.points_available)]
        }
    });
    noUiSlider.create(bet.sliders.advanced_redcards, {
        start: [0],
        step: 10,
        connect: 'lower',
        range: {
            'min': [0],
            'max': [parseInt(bet.points_available)]
        }
    });
    noUiSlider.create(bet.sliders.advanced_defenses, {
        start: [0],
        step: 10,
        connect: 'lower',
        range: {
            'min': [0],
            'max': [parseInt(bet.points_available)]
        }
    });

    $(".slider.advanced").each(function(){
        this.noUiSlider.on('update', function() {
            var total = 0;

            $(".slider.advanced").each(function () {
                total += parseInt(this.noUiSlider.get());
            });

            if (total > app.user.points)
                total = app.user.points;

            bet.advanced_total = total;
        });
    });

    setupSliderTooltip(bet.sliders.simple);
    setupSliderTooltip(bet.sliders.advanced_goals);
    setupSliderTooltip(bet.sliders.advanced_yellowcards);
    setupSliderTooltip(bet.sliders.advanced_redcards);
    setupSliderTooltip(bet.sliders.advanced_defenses);
}

function setupSliderTooltip(slider) {
    var tipHandle = slider.getElementsByClassName('noUi-handle')[0];
    var tooltip = document.createElement('div');
    tipHandle.appendChild(tooltip);
    tooltip.className += 'tooltip';
    tooltip.innerHTML = '<span class="points"></span>';
    tooltip = tooltip.getElementsByTagName('span')[0];

    // When the slider changes, write the value to the tooltips.
    slider.noUiSlider.on('update', function( values, handle ){
        tooltip.innerHTML = values[handle];
    });
}