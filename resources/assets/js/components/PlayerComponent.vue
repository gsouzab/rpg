<template>
    <div class="card">
        <div class="card-header">{{ name }} <span class="float-right">HP: {{stats.hp}}/{{stats.maxHp}}</span></div>
        <div class="progress">
            <div class="progress-bar bg-success progress-bar-animated" role="progressbar"  v-bind:style="{ width: hpPercent + '%' }"></div>
        </div>
        <img class="card-img-top" :src=img :alt=name>
        <div class="card-body">
            <h5 class="card-title">Stats</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">Agilidade: {{ stats.agility }}</li>
                <li class="list-group-item">Força: {{ stats.strength }}</li>                
            </ul>
            <h5 class="card-title mt-3">Arma</h5>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">Dano Máximo: {{ weapon.max_damage }}</li>
                <li class="list-group-item">Ataque: {{ weapon.attack }}</li>
                <li class="list-group-item">Defesa: {{ weapon.defense }}</li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            stats: {
                type: Object
            },
            weapon: {
                type: Object
            },
            img: {
                type: String
            },
            name:  {
                type: String
            }
        },
        computed: {
            hpPercent: function () {
                return this.stats.hp < 0 ? 0 : ((this.stats.hp / this.stats.maxHp) * 100);
            }
        }
    }
</script>

<style scoped>
    .progress {
        border-radius: 0;
    }

    .card-img-top {
        margin: 0 auto;
        /* width: 230px; */
    }

    .list-group-item {
        padding: 0.2rem 1rem;
    }

    .list-group-item:last-child {
        border-bottom: none; 
    }
</style>

