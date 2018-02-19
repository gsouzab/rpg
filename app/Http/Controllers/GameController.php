<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class GameController extends Controller
{
    // the game definitions
    protected $game;

    const STARTED = 1;
    const ENDED = -1;

    function __construct() {
        $this->middleware(function ($request, $next) {
            
            if ($game = Redis::get('game')) {
                $this->game = unserialize($game);
            } else {
                $this->game = $this->initGame();
            }

            if ($this->game["status"] === self::ENDED) {
                return response($this->game);
            }

            return $next($request);
        });
    }


    /**
     * Simulates a dice rolling
     * @param integer $max The maximum value of the dice (included)
     * @return integer N Random number between 1 and $max
     */
    private function rollDice($max = 20) {
        return rand(1, $max);
    }
    
    private function initGame() {
        return [
            "status" => self::STARTED,
            "winner" => null,
            "players" => [
                "human" => [
                    "name" => "human",
                    "hp" => 12,
                    "strength" => 1,
                    "agility" => 2,                    
                    "weapon" => [
                        "max_damage" => 6,
                        "attack" => 2,
                        "defense" => 1
                    ]
                ],
                "orc" => [
                    "name" => "orc",
                    "hp" => 20,
                    "strength" => 2,
                    "agility" => 0,                    
                    "weapon" => [
                        "max_damage" => 8,
                        "attack" => 1,
                        "defense" => 0
                    ]
                ]
            ]
        ];
    }

    /**
     * Persists the game array into Redis
     */
    private function updateGame() {
        Redis::set('game', serialize($this->game));
    }
    
    /**
     * Calculates the damage inflicted by a player
     * The damage will be calculated by simulating a dice thrown (maximum weapon damange) plus the player strength points
     * @param Player $player The player attacking
     * @return integer N The damage of the attack
     */
    private function calculateDamage($player) {
        return $this->rollDice($player["weapon"]["max_damage"]) + $player["strength"];
    }
    
    public function startRound() {
        return [
            "data" => 
            [
                "human" => [
                    "dice" => $this->rollDice(), 
                    "agility" => $this->game["players"]["human"]["agility"]
                ],
                "orc" => [
                    "dice" => $this->rollDice(),
                    "agility" => $this->game["players"]["orc"]["agility"]
                ] 
            ]
        ];
    }

    private function attackPlayer($attacker, $defender) {
        $hasDamage = ($this->rollDice() + $attacker["agility"] + $attacker["weapon"]["attack"]) >
                        ($this->rollDice() + $defender["agility"] + $defender["weapon"]["defense"]);   
        
        $damage = 0;

        if ($hasDamage) {
            $damage = $this->calculateDamage($attacker);
            $defender["hp"] -= $damage;
        }

        return [$damage, $defender];
    }

    private function endGame($winner) {
        $this->game["winner"] = $winner;
        $this->game["status"] = self::ENDED;
    }

    /**
     * Calculates the damage inflicted by a player
     * The damage will be calculated by simulating a dice thrown (maximum weapon damange) plus the player strength points
     * @param string $firstPlayerName The first player to attack
     * @param string $secondPlayerName The second player to attack
     * @return Game The game
     */
    public function attackRound($firstPlayerName, $secondPlayerName) {

        $firstPlayer = $this->game["players"][$firstPlayerName];
        $secondPlayer = $this->game["players"][$secondPlayerName];
        $firstPlayerDamageTaken = 0;
        $secondPlayerDamageTaken = 0;

        list($secondPlayerDamageTaken, $secondPlayer) = $this->attackPlayer($firstPlayer, $secondPlayer);               
        $this->game["players"][$secondPlayerName] = $secondPlayer;

        if ($secondPlayer["hp"] > 0) {
            list($firstPlayerDamageTaken, $firstPlayer) = $this->attackPlayer($secondPlayer, $firstPlayer);
            $this->game["players"][$firstPlayerName] = $firstPlayer;

            if ($firstPlayer["hp"] <= 0) {
                $this->endGame($secondPlayer);    
            }
        } else {
            $this->endGame($firstPlayer);
        }

        $this->updateGame();

        return [
            "firstPlayerDamageTaken" => $firstPlayerDamageTaken,
            "secondPlayerDamageTaken" => $secondPlayerDamageTaken,
            "game" => $this->game
        ];
    }

    public function restart() {
        $this->game = $this->initGame();
        $this->updateGame();

        return $this->game;
    }

    public function play() {
        return $this->game;
    }


}
