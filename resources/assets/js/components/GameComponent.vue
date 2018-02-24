<template>
    <div class="row justify-content-center pt-3">
        <div class="col-md-3">
            <player-component 
                name="Orc"
                :stats=game.players.orc.stats
                :weapon=game.players.orc.weapon
                img="/img/orc.png">
            </player-component>
        </div>

        <div class="col-md-3 controls-container">
            <div class="dices-control">  
                <div class="dice float-left">
                    {{orcDice}}
                </div>
            
                <div class="dice float-right">
                    {{humanDice}}
                </div>                
            </div>

            <div class="clearfix"></div>

            <div class="buttons-control">
                <div class="float-md-left">
                    <button class="btn btn-lg btn-outline-success" v-on:click="attack(game.players.orc.name, game.players.human.name)" v-show="game.players.orc.canAttack">Atacar</button>
                </div>

                <div class="float-md-right">
                    <button class="btn btn-lg btn-outline-success" v-on:click="attack(game.players.human.name, game.players.orc.name)" v-show="game.players.human.canAttack">Atacar</button>
                </div>

                <button class="btn btn-lg btn-outline-primary" v-on:click="startRound()" v-show="canStartRound">Rolar dados</button>
                <button class="btn btn-lg btn-outline-danger" v-on:click="restart()" v-show="canRestart">Reiniciar rodada</button>
            </div>
        </div>

        <div class="col-md-3">
            <player-component 
                name="Humano"
                :stats=game.players.human.stats
                :weapon=game.players.human.weapon
                img="/img/human.png">
            </player-component>
        </div>

        <div class="col-md-3 logs-container">
            
            <h5 class="card-title">Histórico</h5>
            <ul class="list-group history-list">
                <li class="list-group-item" v-for="item in messages">{{item.message}}</li>                
            </ul>
        </div>
    </div>
</template>

<script>
    import PlayerComponent from './PlayerComponent';
    
    const traslatedName = {
        orc: "Orc",
        human: "Humano"
    };

    export default {        
        data: function() {
            return {
                messages: [],
                attacksCount: 0,
                round: 1,
                canStartRound: true,
                canRestart: false,
                humanDice: "",
                orcDice: "",
                history: "",
                game: {
                    players: {
                        human: {
                            canAttack: false,
                            stats: {},
                            weapon: {}
                        },
                        orc: {
                            canAttack: false,
                            stats: {},
                            weapon: {}
                        }
                    }
                }
            }            
        },
        components: {
            PlayerComponent
        },
        mounted() {
            this.$http.get('/api/game').then(response => {
                this.game = response.body;

                if (this.game.status == -1) {
                    this.canRestart = true;
                    this.canStartRound = false;
                    this.appendHistory("Jogo finalizado!")
                } else {
                    this.appendHistory("Jogo iniciou!")
                }
            });
        },
        methods: {
            startRound: function() {
                this.$http.get(`/api/game/startRound`).then( response => {
                    this.humanDice = response.body.data.human.dice;
                    this.orcDice = response.body.data.orc.dice;
                    this.canStartRound = false;
                    
                    let humanStart = this.humanDice + this.game.players.human.stats.agility;
                    let orcStart = this.orcDice + this.game.players.orc.stats.agility;

                    if (humanStart > orcStart) {
                        this.appendHistory(`Rodada ${this.round}: Humano inicia ataque.`)
                        this.game.players.human.canAttack = true;
                        this.round++;
                    } else if (humanStart < orcStart){
                        this.appendHistory(`Rodada ${this.round}: Orc inicia ataque.`)
                        this.game.players.orc.canAttack = true;
                        this.round++;
                    } else {
                        this.appendHistory(`Rodada ${this.round}: Empate, rolando dados novamente.`)
                        this.canStartRound = true;
                    }
                })
            },
            appendHistory: function(message) {
                this.messages.push({message: message});
            },
            clearHistory: function() {
                this.messages = [];
                this.appendHistory("Jogo iniciou!")
            },
            attack: function(attackerName, defenderName) {
                this.$http.get(`/api/game/attackPlayer/attacker/${attackerName}/defender/${defenderName}`).then( response => {
                    this.game = response.body.game;
                    this.attacksCount++;

                    let damage = response.body.damage
                    let message = damage == 0 ? `${traslatedName[attackerName]} ataca: ${traslatedName[defenderName]} defendeu!` : `${traslatedName[attackerName]} ataca: Causou ${damage} de dano!`

                    this.appendHistory(message);

                    if (this.game.status == -1) {
                        this.endGame(response.body.game.winner);
                        return;
                    }

                    if (this.attacksCount >= 2) {
                        this.canStartRound = true;
                        this.game.players[attackerName].canAttack = false;
                        this.game.players[defenderName].canAttack = false;
                        this.attacksCount = 0;
                    } else {
                        this.game.players[attackerName].canAttack = false;
                        this.game.players[defenderName].canAttack = true;
                    }

                })
            },
            endGame: function(winner) {
                this.canRestart = true;
                this.canStartRound = false;
                this.appendHistory(`Rodada encerrada: ${traslatedName[winner.name]} venceu!`)
            },
            restart: function() {
                this.$http.get(`/api/game/restart`).then( response => {
                    this.game = response.body;

                    this.canRestart = false;
                    this.canStartRound = true;

                    this.humanDice = "";
                    this.orcDice = "";

                    this.attacksCount = 0;

                    this.clearHistory();
                })
            },
        }
    }
</script>

<style scoped>
    .dice {
        font-family: Helvetica, Arial, sans-serif;      
        height: 100px;
        width: 100px;
        border: 1px solid gray;
        border-radius: 10px;  
        font-size: 60px;
        text-align: center;

    }

    .history-list {
        max-height: 640px;
        overflow: auto;
    }

    .controls-container {
        margin-top: 10rem;
        display: inline-block;
    }

    .buttons-control {
        padding: 0 8px;
        margin-top: 2rem; 
        text-align: center;
    }
</style>
