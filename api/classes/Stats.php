<?php

class Stats
{
    public $total_bets_placed;
    public $total_bets_won;
    public $total_bets_lost;
    public $total_points_won;
    public $total_points_lost;
    public $avg_bet_value;
    // Charts
    public $wealth_gap;

    /**
     * Get all statistics
     * @param int $user_id
     * @return mixed|bool
     */
    static function Get($user_id = null)
    {
        $stats = new Stats();
        $stats->total_bets_placed = 0;
        $stats->total_bets_won = 0;
        $stats->total_bets_lost = 0;
        $stats->total_points_won = 0;
        $stats->total_points_lost = 0;
        $stats->avg_bet_value = 0;

        $bets = Bet::GetAll();

        $totals = [];

        foreach ($bets as $bet) {
            $stats->total_bets_placed++;
            if ($bet->result == 1) {
                $stats->total_bets_won++;
                $stats->total_points_won += $bet->points_total;
            }
            if ($bet->result == 0) {
                $stats->total_bets_lost++;
                $stats->total_points_lost += $bet->points_total;
            }
            $totals[] = $bet->points_total;
        }

        $stats->avg_bet_value = mmmr($totals, 'median');

        $stats->wealth_gap = wealthGap();

        return $stats;
    }

    /**
     * Get all stats by user
     * @param int $user_id
     * @return mixed|bool
     */
    static function GetByUser($user_id)
    {
        $stats = new Stats();
        $stats->total_bets_placed = 0;
        $stats->total_bets_won = 0;
        $stats->total_bets_lost = 0;
        $stats->total_points_won = 0;
        $stats->total_points_lost = 0;
        $stats->avg_bet_value = 0;

        $bets = Bet::GetAll();

        $totals = [];

        foreach ($bets as $bet) {
            if ($user_id == $bet->user_id) {
                $stats->total_bets_placed++;
                if ($bet->result == 1) {
                    $stats->total_bets_won++;
                    $stats->total_points_won += $bet->points_total;
                }
                if ($bet->result == 0) {
                    $stats->total_bets_lost++;
                    $stats->total_points_lost += $bet->points_total;
                }
                $totals[] = $bet->points_total;
            }
        }

        $stats->avg_bet_value = mmmr($totals, 'median');

        $stats->wealth_gap = wealthGap();

        return $stats;
    }
}

// Calculate points quartiles and count how many users belong to each one
function wealthGap() {
    $user_balance = [];
    $users = User::GetAll();
    foreach ($users as $user)
        $user_balance[] = $user->points;

    $q1 = mmmr($user_balance, 'q1');
    $q2 = mmmr($user_balance, 'median');
    $q3 = mmmr($user_balance, 'q3');

    $users_class1 = 0;
    $users_class2 = 0;
    $users_class3 = 0;
    $users_class4 = 0;

    foreach ($users as $user) {
        if ($user->points < $q1) $users_class1++;
        elseif ($user->points >= $q1 && $user->points < $q2) $users_class2++;
        elseif ($user->points >= $q2 && $user->points < $q3) $users_class3++;
        elseif ($user->points >= $q3) $users_class4++;
    }

    return [
        'q1' => $q1,
        'q2' => $q2,
        'q3' => $q3,
        'users_class1' => $users_class1,
        'users_class2' => $users_class2,
        'users_class3' => $users_class3,
        'users_class4' => $users_class4
    ];
}

// Mean, Median, Mode, Range and first and second quartiles
function mmmr($array, $output = 'mean'){
    if(!is_array($array)){
        return FALSE;
    }else{
        switch($output){
            case 'mean':
                $count = count($array);
                $sum = array_sum($array);
                $total = $sum / $count;
                break;
            case 'median':
                rsort($array);
                $middle = round(count($array) / 2);
                $total = $array[$middle-1];
                break;
            case 'q1':
                sort($array);
                $q1 = round( .25 * ( count($array) + 1 ) );
                $total = $array[$q1-1];
                break;
            case 'q3':
                sort($array);
                $q3 = round( .75 * ( count($array) + 1 ) );
                $total = $array[$q3-1];
                break;
            case 'mode':
                $v = array_count_values($array);
                arsort($v);
                foreach($v as $k => $v){$total = $k; break;}
                break;
            case 'range':
                sort($array);
                $sml = $array[0];
                rsort($array);
                $lrg = $array[0];
                $total = $lrg - $sml;
                break;
        }
        return $total;
    }
}