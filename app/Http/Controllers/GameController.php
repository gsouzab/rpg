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
        })->except('restart');
    }


    /**
     * Simulates a dice rolling
     * @param integer $max The maximum value of the dice (included)
     * @return integer N Random number between 1 and $max
     */
    private function rollDice($max = 20) {
        return rand(1, $max);
    }
    
    /**
     * Initialize game
     * Starts the game with initial data
     * @return Game The game
     */
    private function initGame() {
        return [
            "status" => self::STARTED,
            "winner" => null,
            "players" => [
                "human" => [
                    "name" => "human",
                    "stats" => [
                        "maxHp" => 12,
                        "hp" => 12,
                        "strength" => 1,
                        "agility" => 2,                    
                    ],                    
                    "weapon" => [
                        "max_damage" => 6,
                        "attack" => 2,
                        "defense" => 1
                    ]
                ],
                "orc" => [
                    "name" => "orc",
                    "stats" => [
                        "maxHp" => 20,
                        "hp" => 20,
                        "strength" => 2,
                        "agility" => 0,                    
                    ],
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
        return $this->rollDice($player["weapon"]["max_damage"]) + $player["stats"]["strength"];
    }
    
    /**
     * Starts a round
     * Roll dices for both players and return its value 
     */
    public function startRound() {
        return [
            "data" => 
            [
                "human" => [
                    "dice" => $this->rollDice(), 
                    "agility" => $this->game["players"]["human"]["stats"]["agility"]
                ],
                "orc" => [
                    "dice" => $this->rollDice(),
                    "agility" => $this->game["players"]["orc"]["stats"]["agility"]
                ] 
            ]
        ];
    }

    /**
     * Performs an attack 
     * The damage will be calculated by simulating a dice thrown (maximum weapon damange) plus the player strength points
     * @param string $attackerName The name of the attacking player
     * @param string $defenderName The name of the defending player
     * @return Game The game
     */
    public function attackPlayer($attackerName, $defenderName) {
        $attacker = $this->game["players"][$attackerName];
        $defender = $this->game["players"][$defenderName];
        
        $hasDamage = ($this->rollDice() + $attacker["stats"]["agility"] + $attacker["weapon"]["attack"]) >
                        ($this->rollDice() + $defender["stats"]["agility"] + $defender["weapon"]["defense"]);   
        
        $damage = 0;

        if ($hasDamage) {
            $damage = $this->calculateDamage($attacker);
            $defender["stats"]["hp"] -= $damage;            
        }

        if ($defender["stats"]["hp"] <= 0) {
            $this->endGame($attacker);
        }

        $this->game["players"][$defenderName] = $defender;
        $this->updateGame();

        return [
            "damage" => $damage,
            "game" => $this->game
        ];
    }

    /**
     * Ends the game
     * @param Player $winner The game winner
     */
    private function endGame($winner) {
        $this->game["winner"] = $winner;
        $this->game["status"] = self::ENDED;
    }

    /**
     * Restarts the game
     * @return Game The game
     */
    public function restart() {
        $this->game = $this->initGame();
        $this->updateGame();

        return $this->game;
    }

    public function play() {
        return $this->game;
    }


}
