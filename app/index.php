<?php
include("template.php");

template_header("dashboard");
?>

<section class="wide">
    <div class="title"><span class="live"></span> Live Matches</div>
    <div class="content">
        <?php
        $matches = mysqli_query($connection, "SELECT * FROM `Match` WHERE StartTime < NOW() AND NOW() < EndTime");
        while ($match = mysqli_fetch_array($matches)) {
            $teams = mysqli_query($connection, "SELECT * FROM Team, MatchProgress WHERE MatchID = $match[ID] AND Team.ID = MatchProgress.TeamID GROUP BY Name");
            $team_details = [];
            while ($team_details[] = mysqli_fetch_array($teams));
            ?>
            <div class="match">
                <div><span class="label green">! IN PROGRESS !</span></div>
                <span class="score"><?=$team_details[0]["Goals"]?></span>
                <span class="label cyan">SCORE</span>
                <span class="score"><?=$team_details[1]["Goals"]?></span>
                <div class="teams">
                    <div class="left-team"><?=$team_details[0]["Name"]?></div>
                    <span class="vs">[VS]</span>
                    <?=$team_details[1]["Name"]?></div>
            </div>
        <?php } ?>
    </div>
</section>

<section class="regular">
    <div class="title">Upcoming Matches</div>
    <div class="content">
        <?php
        $matches = mysqli_query($connection, "SELECT * FROM `Match` WHERE StartTime > NOW()");
        while ($match = mysqli_fetch_array($matches)) {
            $teams = mysqli_query($connection, "SELECT * FROM Team, MatchProgress WHERE MatchID = $match[ID] AND Team.ID = MatchProgress.TeamID GROUP BY Name");
            $team_details = [];
            while ($team_details[] = mysqli_fetch_array($teams));
            ?>
            <div class="match">
                <div><span class="label green">STARTING ON</span> <span class="time"><?=$match["StartTime"]?></span></div>
                <div class="teams">
                    <div class="left-team"><?=$team_details[0]["Name"]?></div>
                    <span class="vs">[VS]</span>
                    <?=$team_details[1]["Name"]?></div>
            </div>
        <?php } ?>
    </div>
</section>

<section class="regular">
    <div class="title">Past Matches</div>
    <div class="content">
        <?php
        $matches = mysqli_query($connection, "SELECT * FROM `Match` WHERE EndTime < NOW()");
        while ($match = mysqli_fetch_array($matches)) {
            $teams = mysqli_query($connection, "SELECT * FROM Team, MatchProgress WHERE MatchID = $match[ID] AND Team.ID = MatchProgress.TeamID GROUP BY Name");
            $team_details = [];
            while ($team_details[] = mysqli_fetch_array($teams));
            ?>
            <div class="match">
                <div><span class="label red">ENDED</span> <span class="time"><?=$match["StartTime"]?></span></div>
                <span class="score"><?=$team_details[0]["Goals"]?></span>
                <span class="label cyan">SCORE</span>
                <span class="score"><?=$team_details[1]["Goals"]?></span>
                <div class="teams">
                    <div class="left-team"><?=$team_details[0]["Name"]?></div>
                    <span class="vs">[VS]</span>
                    <?=$team_details[1]["Name"]?></div>
            </div>
        <?php } ?>
    </div>
</section>

<?php
template_footer();