Vue.component('tournament-table', {
    props: {
        tournaments: Array,
        headers: Array,
        tableId: String,
        emptyMessage: String,
        isLoaded: Boolean,
        tournamentCount: Number, 
        userAuth: {
            type: Boolean,
            default: false
        }
    },
    template: `
    <div v-lazy-container="{ selector: 'img' }">
        <table class="table table-sm table-striped abr-table table-doublerow" :id="tableId">
            <thead>
                <th v-if="headers.includes('title')">title</th>
                <th v-if="headers.includes('date')">date</th>
                <th v-if="headers.includes('location')" class="hidden-sm-down">location</th>
                <th v-if="headers.includes('location')" class="text-xs-center hidden-md-up">
                    <i class="fa fa-globe" title="location"></i>
                </th>
                <th v-if="headers.includes('cardpool')">cardpool</th>
                <th v-if="headers.includes('winner')" class="text-xs-center">1st</th>
                <th v-if="headers.includes('players')" class="text-xs-center">
                    <span class="hidden-sm-down">players</span>
                    <i class="fa fa-user hidden-xs-down hidden-md-up" title="players"></i>
                </th>
                <th v-if="headers.includes('claims')" class="text-xs-center">
                    <span class="hidden-sm-down">claims</span>
                    <i class="fa fa-address-card hidden-xs-down hidden-md-up" title="claims"></i>
                </th>
                <th v-if="headers.includes('conclusion')" class="text-xs-center">conclusion</th>
                <th v-if="headers.includes('regs')" class="text-xs-center">
                    <span class="hidden-sm-down">regs</span>
                    <i class="hidden-xs-down hidden-md-up fa fa-registered" title="registered"></i>
                </th>
            </thead>
            <tbody>
                <tr v-if="tournaments.length == 0 && isLoaded">
                    <td :colspan="headers.length" class="text-xs-center small-text">{{ emptyMessage }}</td>
                </tr>
                <tr v-for="tournament in tournaments.slice(fromIndex-1, toIndex)" :key="tournament.id">
                    <td class="tournament-title" v-if="headers.includes('title')">
                        <i title="charity" class="fa fa-heart text-danger" v-if="tournament.charity"></i>
                        <span class="tournament-type type-store" title="store championship" v-if="tournament.type == 'store championship'">S</span>
                        <span class="tournament-type type-regional" title="regional championship" v-if="tournament.type == 'regional championship'">R</span>
                        <span class="tournament-type type-national" title="national championship" v-if="tournament.type == 'national championship'">N</span>
                        <span class="tournament-type type-continental" title="continental championship" v-if="tournament.type == 'continental championship'">C</span>
                        <span class="tournament-type type-world" title="worlds championship" v-if="tournament.type == 'worlds championship'">W</span>
                        <span class="tournament-type type-team" title="team tournament" v-if="tournament.type == 'team tournament'">TT</span>
                        <span class="tournament-format type-startup" title="startup" v-if="tournament.format == 'startup'">SU</span>
                        <span class="tournament-format type-snapshot" title="snapshot" v-if="tournament.format == 'snapshot'">SN</span>
                        <span class="tournament-format type-eternal" title="eternal" v-if="tournament.format == 'eternal'">E</span>
                        <span class="tournament-format type-other" title="other" v-if="tournament.format == 'other'">?</span>
                        <span class="tournament-format type-cube-draft" title="cube draft" v-if="tournament.format == 'cube draft'">CD</span>
                        <span class="tournament-format type-cache" title="cache refresh" v-if="tournament.format == 'cache refresh'">CR</span>
                        <span class="tournament-format type-onesies" title="1.1.1.1" v-if="tournament.format == '1.1.1.1'">1</span>
                        <span class="tournament-format type-draft" title="draft" v-if="tournament.format == 'draft'">D</span>
                        <a :href="tournament.url.substr(tournament.url.indexOf('/tournaments'))" v-bind:class="{ 'font-italic': !tournament.approved }">
                            {{ tournament.title }}
                        </a>
                        <span class="text-nowrap">
                            <img class="img-patron-o" v-if="parseInt(tournament.creator_supporter) > 2">
                            <i class="fa fa-handshake-o" title="match data" v-if="tournament.matchdata"></i>
                            <i class="fa fa-scissors" title="top cut" v-if="tournament.top_count > 0"></i>
                            {{ tournament.photos > 1 ? tournament.photos : ''}}<i class="fa fa-camera" title="photo" v-if="tournament.photos > 0"></i>
                            {{ tournament.videos > 1 ? tournament.videos : ''}}<i class="fa fa-video-camera" title="video" v-if="tournament.videos > 0"></i>
                        </span>
                    </td>
                    <td v-if="headers.includes('date')">
                        <span class="line-breaker">{{ tournament.date ? tournament.date.substring(0, 5) : tournament.recurring_day }}</span><span class="line-breaker">{{ tournament.date ? tournament.date.substring(5) : '' }}<i class="fa fa-plus-circle icon-upper" title="multiple day event" v-if="tournament.end_date"></i></span>
                    </td>
                    <td v-if="headers.includes('location')" class="text-center text-md-left">
                        <template v-if="tournament.location === 'online'">
                            <img v-if="showFlag" class="country-flag" data-src="/img/flags/online.png" title="online"><span v-if="showFlag" class="hidden-sm-down">online</span><span v-if="!showFlag">online</span>
                        </template>
                        <template v-else>
                            <template v-if="tournament.location_country in countryFlags && countryFlags[tournament.location_country] != null">
                                <img v-if="showFlag" class="country-flag" :data-src="'/img/flags/' + countryFlags[tournament.location_country]" :title="tournament.location_country"><span v-if="!showFlag">{{ tournament.location_country }}</span><span class="hidden-sm-down" v-if="!showFlag">, </span><span class="hidden-sm-down">{{ tournament.location.substr(tournament.location.indexOf(', ')+2) }}</span>
                            </template>
                            <template v-else>
                                <span class="hidden-sm-down">{{ tournament.location }}</span><span class="hidden-md-up">{{ tournament.location_country }}</span>
                            </template>
                        </template>
                    </td>
                    <td v-if="headers.includes('cardpool')">
                        <span v-if="tournament.cardpool !=='- not yet known -'">{{ tournament.cardpool }}</span>
                        <span v-else class="text-danger">not&nbsp;yet&nbsp;known</span>
                    </td>
                    <td v-if="headers.includes('winner')" class="text-xs-center cell-winner-v">
                        <img :data-src="'/img/ids/'+tournament.winner_runner_identity+'.png'" v-if="tournament.winner_runner_identity" alt="">
                        <img :data-src="'/img/ids/'+tournament.winner_corp_identity+'.png'" v-if="tournament.winner_corp_identity" alt="">
                    </td>
                    <td v-if="headers.includes('conclusion')" class="text-xs-center">
                        <span v-if="tournament.concluded" class="label label-success">concluded</span>
                        <template v-else-if="tournament.date <= nowDate">
                            <button v-if="userAuth" class="btn btn-conclude btn-xs" data-toggle="modal" data-target="#concludeModal" :data-tournament-id="tournament.id"
                                    :data-subtitle="tournament.title + ' - ' + tournament.date">
                                <i class="fa fa-check" aria-hidden></i>
                                conclude
                            </button>
                            <em v-if="!userAuth">waiting</em>
                        </template>
                        <span v-else class="label label-info">not yet</span>
                    </td>
                    <td v-if="headers.includes('players') || headers.includes('regs')" class="text-xs-center hidden-xs-down">
                        {{ tournament.concluded ? tournament.players_count : tournament.registration_count }}
                    </td>
                    <td v-if="headers.includes('claims')" class="text-xs-center hidden-xs-down">
                        <i class="fa fa-exclamation-triangle text-danger" title="conflict" v-if="tournament.claim_conflict"></i>
                        {{ tournament.claim_count }}
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="text-xs-center small-text" :id="tableId+'-paging'" v-if="tournaments.length > 0 || isLoaded">
            <a class="fake-link" @click="paging(-pageWith)" v-if="fromIndex > 1" :id="tableId+'-paging-forward'">
                <i class="fa fa-chevron-circle-left" aria-hidden="true"></i>
            </a>
            showing {{ fromIndex }}-{{ toIndex }} of {{ isLoaded ? tournamentCount : '...loading...' }}
            <a class="fake-link" @click="paging(pageWith)" v-if="toIndex < tournaments.length" :id="tableId+'-paging-back'">
                <i class="fa fa-chevron-circle-right" aria-hidden="true"></i>
            </a>
        </div>
        <div class="text-xs-center" :id="tableId+'-options'" style="font-size: 80%; font-style: normal">
            <span class="label control-paging" :class="pageWith == 50 ? 'label-active' : 'label-inactive'" @click="changePageOption(50)" :id="tableId+'-option-50'">
                50
            </span>
            <span class="label control-paging" :class="pageWith == 100 ? 'label-active' : 'label-inactive'" @click="changePageOption(100)" :id="tableId+'-option-100'">
                100
            </span>
            <span class="label control-paging" :class="pageWith == 500 ? 'label-active' : 'label-inactive'" @click="changePageOption(500)" :id="tableId+'-option-500'">
                500
            </span>
            <span> - </span>
            <span class="label control-flag" :class="showFlag ? 'label-active' : 'label-inactive'" @click="changeFlagOption(true)" :id="tableId+'-option-flag'">flag</span>
            <span class="label control-text" :class="!showFlag ? 'label-active' : 'label-inactive'" @click="changeFlagOption(false)" :id="tableId+'-option-text'">text</span>
        </div>
    </div>
        `,
    methods: {
        paging: function (modifyIndex) {
            this.fromIndex += modifyIndex
            if (this.fromIndex < 1) {
                this.fromIndex = 1
                this.toIndex = this.pageWith
            }
            this.toIndex = this.fromIndex + this.pageWith - 1
            if (this.toIndex > this.tournamentCount) this.toIndex = this.tournamentCount
            if (modifyIndex > 0) this.$emit('pageforward', this.toIndex)
        },
        changePageOption: function (pageOption) {
            this.pageWith = parseInt(pageOption)
            this.fromIndex = 1
            this.toIndex = Math.min(this.pageWith, this.tournamentCount)
            setCookie('pager-'+this.tableId+'-option', pageOption)
            this.$emit('pageforward', this.toIndex)
        },
        changeFlagOption: function (flagOption) {
            this.showFlag = flagOption
            setCookie('showflag', flagOption)
        }
    },
    computed: {
        nowDate: function () {
            const date = new Date()
            const m = date.getMonth() + 1
            const d = date.getDate()
            return `${date.getFullYear()}.${m < 10 ? '0' : ''}${m}.${d < 10 ? '0' : ''}${d}.`
        }
    },
    data: function() {
        return {
            showFlag: getCookie('showflag').length ? getCookie('showflag') === 'true' : true,
            pageWith: getCookie('pager-'+this.tableId+'-option').length ? parseInt(getCookie('pager-'+this.tableId+'-option')) : 50,
            fromIndex: 1,
            toIndex: 50,
            countryFlags: { // TODO: import this from abr-flags.js
                "Afghanistan": "AF.png",
                "Åland Islands": null,
                "Albania": "AL.png",
                "Algeria": "DZ.png",
                "American Samoa": "AS.png",
                "Andorra": "AD.png",
                "Angola": "AO.png",
                "Anguilla": "AI.png",
                "Antarctica": "AQ.png",
                "Antigua and Barbuda": "AG.png",
                "Argentina": "AR.png",
                "Armenia": "AM.png",
                "Aruba": "AW.png",
                "Australia": "AU.png",
                "Austria": "AT.png",
                "Azerbaijan": "AZ.png",
                "Bahamas": "BS.png",
                "Bahrain": "BH.png",
                "Bangladesh": "BD.png",
                "Barbados": "BB.png",
                "Belarus": "BY.png",
                "Belgium": "BE.png",
                "Belize": "BZ.png",
                "Benin": "BJ.png",
                "Bermuda": "BM.png",
                "Bhutan": "BT.png",
                "Bolivia, Plurinational State of": "BO.png",
                "Bonaire, Sint Eustatius and Saba": null,
                "Bosnia and Herzegovina": "BA.png",
                "Botswana": "BW.png",
                "Bouvet Island": "BV.png",
                "Brazil": "BR.png",
                "British Indian Ocean Territory": "IO.png",
                "Brunei Darussalam": "BN.png",
                "Bulgaria": "BG.png",
                "Burkina Faso": "BF.png",
                "Burundi": "BI.png",
                "Cambodia": "KH.png",
                "Cameroon": "CM.png",
                "Canada": "CA.png",
                "Cape Verde": "CV.png",
                "Cayman Islands": "KY.png",
                "Central African Republic": "CF.png",
                "Chad": "TD.png",
                "Chile": "CL.png",
                "China": "CN.png",
                "Christmas Island": "CX.png",
                "Cocos (Keeling) Islands": "CC.png",
                "Colombia": "CO.png",
                "Comoros": "KM.png",
                "Congo": "CG.png",
                "Congo, the Democratic Republic of the": "CD.png",
                "Cook Islands": "CK.png",
                "Costa Rica": "CR.png",
                "Côte d'Ivoire": "CI.png",
                "Croatia": "HR.png",
                "Cuba": "CU.png",
                "Curaçao": null,
                "Cyprus": "CY.png",
                "Czech Republic": "CZ.png",
                "Czechia": "CZ.png",
                "Denmark": "DK.png",
                "Djibouti": "DJ.png",
                "Dominica": "DM.png",
                "Dominican Republic": "DO.png",
                "Ecuador": "EC.png",
                "Egypt": "EG.png",
                "El Salvador": "SV.png",
                "Equatorial Guinea": "GQ.png",
                "Eritrea": "ER.png",
                "Estonia": "EE.png",
                "Ethiopia": "ET.png",
                "Falkland Islands (Malvinas)": "FK.png",
                "Faroe Islands": "FO.png",
                "Fiji": "FJ.png",
                "Finland": "FI.png",
                "France": "FR.png",
                "French Guiana": "GF.png",
                "French Polynesia": "PF.png",
                "French Southern Territories": "TF.png",
                "Gabon": "GA.png",
                "Gambia": "GM.png",
                "Georgia": "GE.png",
                "Germany": "DE.png",
                "Ghana": "GH.png",
                "Gibraltar": "GI.png",
                "Greece": "GR.png",
                "Greenland": "GL.png",
                "Grenada": "GD.png",
                "Guadeloupe": "GP.png",
                "Guam": "GU.png",
                "Guatemala": "GT.png",
                "Guernsey": null,
                "Guinea": "GN.png",
                "Guinea-Bissau": "GW.png",
                "Guyana": "GY.png",
                "Haiti": "HT.png",
                "Heard Island and McDonald Islands": "HM.png",
                "Holy See (Vatican City State)": "VA.png",
                "Honduras": "HN.png",
                "Hong Kong": "HK.png",
                "Hungary": "HU.png",
                "Iceland": "IS.png",
                "India": "IN.png",
                "Indonesia": "ID.png",
                "Iran, Islamic Republic of": "IR.png",
                "Iraq": "IQ.png",
                "Ireland": "IE.png",
                "Isle of Man": null,
                "Israel": "IL.png",
                "Italy": "IT.png",
                "Jamaica": "JM.png",
                "Japan": "JP.png",
                "Jersey": null,
                "Jordan": "JO.png",
                "Kazakhstan": "KZ.png",
                "Kenya": "KE.png",
                "Kiribati": "KI.png",
                "Korea, Democratic People's Republic of": "KP.png",
                "Korea, Republic of": "KR.png",
                "South Korea": "KR.png",
                "Kuwait": "KW.png",
                "Kyrgyzstan": "KG.png",
                "Lao People's Democratic Republic": "LA.png",
                "Latvia": "LV.png",
                "Lebanon": "LB.png",
                "Lesotho": "LS.png",
                "Liberia": "LR.png",
                "Libya": "LY.png",
                "Liechtenstein": "LI.png",
                "Lithuania": "LT.png",
                "Luxembourg": "LU.png",
                "Macao": "MO.png",
                "Macedonia, the former Yugoslav Republic of": "MK.png",
                "Madagascar": "MG.png",
                "Malawi": "MW.png",
                "Malaysia": "MY.png",
                "Maldives": "MV.png",
                "Mali": "ML.png",
                "Malta": "MT.png",
                "Marshall Islands": "MH.png",
                "Martinique": "MQ.png",
                "Mauritania": "MR.png",
                "Mauritius": "MU.png",
                "Mayotte": "YT.png",
                "Mexico": "MX.png",
                "Micronesia, Federated States of": "FM.png",
                "Moldova, Republic of": "MD.png",
                "Monaco": "MC.png",
                "Mongolia": "MN.png",
                "Montenegro": null,
                "Montserrat": "MS.png",
                "Morocco": "MA.png",
                "Mozambique": "MZ.png",
                "Myanmar": "MM.png",
                "Namibia": "NA.png",
                "Nauru": "NR.png",
                "Nepal": "NP.png",
                "Netherlands": "NL.png",
                "New Caledonia": "NC.png",
                "New Zealand": "NZ.png",
                "Nicaragua": "NI.png",
                "Niger": "NE.png",
                "Nigeria": "NG.png",
                "Niue": "NU.png",
                "Norfolk Island": "NF.png",
                "Northern Mariana Islands": "MP.png",
                "Norway": "NO.png",
                "Oman": "OM.png",
                "Pakistan": "PK.png",
                "Palau": "PW.png",
                "Palestinian Territory, Occupied": "PS.png",
                "Panama": "PA.png",
                "Papua New Guinea": "PG.png",
                "Paraguay": "PY.png",
                "Peru": "PE.png",
                "Philippines": "PH.png",
                "Pitcairn": "PN.png",
                "Poland": "PL.png",
                "Portugal": "PT.png",
                "Puerto Rico": "PR.png",
                "Qatar": "QA.png",
                "Réunion": "RE.png",
                "Romania": "RO.png",
                "Russian Federation": "RU.png",
                "Russia": "RU.png",
                "Rwanda": "RW.png",
                "Saint Barthélemy": null,
                "Saint Helena, Ascension and Tristan da Cunha": "SH.png",
                "Saint Kitts and Nevis": "KN.png",
                "Saint Lucia": "LC.png",
                "Saint Martin (French part)": null,
                "Saint Pierre and Miquelon": "PM.png",
                "Saint Vincent and the Grenadines": "VC.png",
                "Samoa": "WS.png",
                "San Marino": "SM.png",
                "Sao Tome and Principe": "ST.png",
                "Saudi Arabia": "SA.png",
                "Senegal": "SN.png",
                "Serbia": null,
                "Seychelles": "SC.png",
                "Sierra Leone": "SL.png",
                "Singapore": "SG.png",
                "Sint Maarten (Dutch part)": null,
                "Slovakia": "SK.png",
                "Slovenia": "SI.png",
                "Solomon Islands": "SB.png",
                "Somalia": "SO.png",
                "South Africa": "ZA.png",
                "South Georgia and the South Sandwich Islands": "GS.png",
                "South Sudan": null,
                "Spain": "ES.png",
                "Sri Lanka": "LK.png",
                "Sudan": null,
                "Suriname": "SR.png",
                "Svalbard and Jan Mayen": "SJ.png",
                "Swaziland": "SZ.png",
                "Sweden": "SE.png",
                "Switzerland": "CH.png",
                "Syrian Arab Republic": "SY.png",
                "Taiwan, Province of China": "TW.png",
                "Tajikistan": "TJ.png",
                "Tanzania, United Republic of": "TZ.png",
                "Thailand": "TH.png",
                "Timor-Leste": "TL.png",
                "Togo": "TG.png",
                "Tokelau": "TK.png",
                "Tonga": "TO.png",
                "Trinidad and Tobago": "TT.png",
                "Tunisia": "TN.png",
                "Turkey": "TR.png",
                "Turkmenistan": "TM.png",
                "Turks and Caicos Islands": "TC.png",
                "Tuvalu": "TV.png",
                "Uganda": "UG.png",
                "Ukraine": "UA.png",
                "United Arab Emirates": "AE.png",
                "United Kingdom": "GB.png",
                "United States": "US.png",
                "United States Minor Outlying Islands": "UM.png",
                "Uruguay": "UY.png",
                "Uzbekistan": "UZ.png",
                "Vanuatu": "VU.png",
                "Venezuela, Bolivarian Republic of": "VE.png",
                "Viet Nam": "VN.png",
                "Virgin Islands, British": "VG.png",
                "Virgin Islands, U.S.": "VI.png",
                "Wallis and Futuna": "WF.png",
                "Western Sahara": "EH.png",
                "Yemen": "YE.png",
                "Zambia": "ZM.png",
                "Zimbabwe": "ZW.png"
            }
        }
    }
})