<div id="vue-tournament-groups">
    {{--My tournament groups--}}
    @include('organize.modal-groups')
    @include('organize.modal-link')
    <confirm-modal :modal-body="confirmText" :callback="confirmCallback"></confirm-modal>
    <div class="row">
        <div class="col-xs-12">
            <div class="bracket">
                <h5>
                    <i class="fa fa-folder-open" aria-hidden="true"></i>
                    <span class="hidden-sm-down">Tournament</span> Groups created by me
                    <a class="btn btn-success pull-right white-text" id="button-create-group"
                       data-toggle="modal" data-target="#modal-group" @click="modalForCreate">
                        Create Group
                    </a>
                </h5>
                <div class="loader" id="my-groups-loader">&nbsp;</div>
                <table class="table table-sm table-striped abr-table table-doublerow hover-row">
                    <thead>
                        <th>title</th>
                        <th class="text-xs-center">
                            <span class="hidden-sm-down">#tournaments</span>
                            <span class="hidden-md-up">#</span>
                        </th>
                        <th>location</th>
                        <th></th>
                    </thead>
                    <tbody>
                        <tr v-if="myGroups.length == 0">
                            <td colspan="4" class="text-xs-center">
                                <em>you have no groups created yet</em>
                            </td>
                        </tr>
                        <tr v-for="group in myGroups" :class="group.id == selectedGroup.id ? 'row-selected': ''"
                                @click.stop="loadGroup(group.id)">
                            <td>
                                @{{ group.title }}
                            </td>
                            <td class="text-xs-center">
                                @{{ group.tournamentCount }}
                                <a class="btn btn-primary white-text btn-xs" id="button-link-group"
                                    @click="linkForm.location = group.location; modalForLink(group);">
                                    <i class="fa fa-link" aria-hidden="true"></i>
                                    link
                                </a>
                            </td>
                            <td>@{{ group.location }}</td>
                            <td class="text-xs-right">
                                <a class="btn btn-primary btn-xs white-text" @click.stop="modalForEdit(group)">
                                    <i class="fa fa-pencil"></i> edit
                                </a>
                                <a class="btn btn-primary btn-xs white-text hidden-lg-up" @click.stop="loadGroup(group.id)">
                                    <i class="fa fa-cogs"></i> select
                                </a>
                                <form method="post" action="" style="display: inline">
                                    <input name="_method" type="hidden" value="DELETE"/>
                                    <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                    <input name="delete_id" type="hidden" :value="group.id">
                                    <confirm-button button-text="delete" button-class="btn btn-danger btn-xs" button-icon="fa fa-trash"
                                        @click="confirmCallback = function() { deleteGroup(group.id) }; confirmText = 'Delete tournament group?'" />
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        {{--Group details--}}
        <div class="col-xs-12 col-lg-4">
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-cogs" aria-hidden="true"></i>
                    <span class="hidden-sm-down">Tournament</span> Group details
                </h5>
                <div class="text-xs-center" v-if="selectedGroup == ''">
                    <em>
                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                        select a group to view its details
                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                    </em>
                </div>
                <div v-if="selectedGroup != ''" class="p-b-1">
                    <strong>title:</strong> @{{ selectedGroup.title }}<br/>
                    <strong>location:</strong> @{{ selectedGroup.location }}<br/>
                    <strong>description:</strong>
                    <div v-html="compiledMarkdown"></div>
                </div>
            </div>
        </div>
        {{--Tournaments in group--}}
        <div class="col-xs-12 col-lg-8">
            <div class="bracket">
                <h5 class="p-b-1">
                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                    Tournaments in Group
                    <a class="btn btn-primary pull-right white-text" id="button-link-group"
                            @click="modalForLink(group)" v-if="selectedGroup != ''">
                        <i class="fa fa-link" aria-hidden="true"></i>
                        Link Tournament
                    </a>
                </h5>
                <div class="text-xs-center" v-if="selectedGroup == ''">
                    <em>
                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                        select a group to view its details
                        <i class="fa fa-chevron-up" aria-hidden="true"></i>
                    </em>
                </div>
                <div v-if="selectedGroup != ''">
                    <table class="table table-sm table-striped abr-table table-doublerow">
                        <thead>
                            <th>date</th>
                            <th>tournament</th>
                            <th></th>
                        </thead>
                        <tbody>
                            <tr v-if="selectedGroup.tournaments.length == 0">
                                <td colspan="3" class="text-xs-center">
                                    <em>no tournaments added yet</em>
                                </td>
                            </tr>
                            <tr v-for="tournament in selectedGroup.tournaments">
                                <td>
                                    @{{ tournament.date }}
                                </td>
                                <td>
                                    <a :href="tournament.seoUrl">
                                        @{{ tournament.title }}
                                    </a>
                                </td>
                                <td class="text-xs-right">
                                    <confirm-button button-text="unlink" button-class="btn btn-danger btn-xs" button-icon="fa fa-unlink"
                                        @click="confirmCallback = function() { unlinkTournament(selectedGroup.id, tournament.id) }; confirmText = 'Unlink tournament from group?'" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    var tournamentGroups = new Vue({
        el: '#vue-tournament-groups',
        data: {
            myGroups: [],
            modalTitle: '',
            editMode: false,
            group: {
                location: 'online'
            },
            selectedGroup: '',
            modalButton: '',
            confirmCallback: function() {},
            confirmText: '',
            linkForm: {
                own: true,
                tournaments: []
            }
        },
        components: {

        },
        computed: {
            compiledMarkdown: function () {
                if (this.selectedGroup == '') {
                    return '';
                }
                return marked(this.selectedGroup.description, { sanitize: true })
            }
        },
        mounted: function() {
            this.loadMyGroups();
        },
        methods: {
            // load all my groups
            loadMyGroups: function() {
                axios.get('/api/tournament-groups?user='+ '{{ $user }}' ).then(function (response) {
                    tournamentGroups.myGroups = response.data;
                    $('#my-groups-loader').addClass('hidden-xs-up');
                }, function(response) {
                    // error handling
                    toastr.error('Something went wrong.', '', {timeOut: 2000});
                });
            },
            // load a particular group
            loadGroup: function(id) {
                axios.get('/api/tournament-groups/'+id).then(function (response) {
                    tournamentGroups.selectedGroup = response.data;
                    // pre-set country of link tournament form
                    if (tournamentGroups.selectedGroup.location == 'multiple countries') {
                        tournamentGroups.linkForm.location = 'all';
                    } else if (tournamentGroups.selectedGroup.location == 'online') {
                        tournamentGroups.linkForm.location = 'online';
                    } else {
                        tournamentGroups.linkForm.location = tournamentGroups.selectedGroup.location;
                    }
                }, function(response) {
                    // error handling
                    toastr.error('Something went wrong.', '', {timeOut: 2000});
                });
            },
            // load tournament options for linking
            loadTournaments: function() {
                var requestURL = '/api/tournaments/brief/?';
                if (this.linkForm.own) {
                    requestURL += 'user=' + '{{ $user }}' + '&';
                }
                if (this.linkForm.location != 'all') {
                    requestURL += 'location=' + this.linkForm.location;
                }
                axios.get(requestURL).then(function (response) {
                    tournamentGroups.linkForm.tournaments = response.data;
                    if (response.data.length > 0) {
                        tournamentGroups.linkForm.tournament = tournamentGroups.linkForm.tournaments[0].id;
                    }
                }, function(response) {
                    // error handling
                    toastr.error('Something went wrong.', '', {timeOut: 2000});
                });
            },
            // open create tournament group modal
            modalForCreate: function() {
                this.modalTitle = 'Create Tournament Group';
                this.modalButton = 'Create';
                this.editMode = false;
            },
            // open edit tournament group modal
            modalForEdit: function(group) {
                tournamentGroups.group = group;
                this.modalTitle = 'Edit Tournament Group';
                this.modalButton = 'Save';
                this.editMode = true;
                $("#modal-group").modal('show');
            },
            // open link tournament modal
            modalForLink: function(group) {
                $("#modal-link-group").modal('show');
                this.loadTournaments();
            },
            // create new tournament group
            createGroup: function() {
                axios.post('/api/tournament-groups', this.group)
                        .then(function(response) {
                            tournamentGroups.loadMyGroups();
                            $("#modal-group").modal('hide');
                            toastr.info('Group created successfully.', '', {timeOut: 2000});
                            tournamentGroups.loadGroup(response.data.id);
                        }, function(response) {
                            // error handling
                            toastr.error('Something went wrong.', '', {timeOut: 2000});
                        }
                );
            },
            // update tournament group
            updateGroup: function() {
                axios.put('/api/tournament-groups/' + this.group.id, this.group)
                    .then(function(response) {
                        $("#modal-group").modal('hide');
                        toastr.info('Group updated successfully.', '', {timeOut: 2000});
                        tournamentGroups.loadGroup(tournamentGroups.group.id);
                    }, function(response) {
                        // error handling
                        toastr.error('Something went wrong.', '', {timeOut: 2000});
                    }
                );
            },
            // delete tournament group
            deleteGroup: function(id) {
                axios.delete('/api/tournament-groups/' + id).then(function (response) {
                    tournamentGroups.loadMyGroups();
                    toastr.info('Tournament Group deleted.', '', {timeOut: 2000});
                    tournamentGroups.selectedGroup = '';
                }, function(response) {
                    // error handling
                    toastr.error('Something went wrong.', '', {timeOut: 2000});
                });
            },
            // links tournament to group
            linkTournament: function() {
                $("#modal-link-group").modal('hide');
                axios.post('/api/tournament-groups/' + this.selectedGroup.id + '/link/' + this.linkForm.tournament)
                        .then(function(response) {
                            toastr.info('Tournament linked successfully.', '', {timeOut: 2000});
                            tournamentGroups.loadGroup(tournamentGroups.selectedGroup.id);
                            tournamentGroups.loadMyGroups();
                        }, function(response) {
                            // error handling
                            toastr.error('Something went wrong.', '', {timeOut: 2000});
                        }
                );
            },
            // unlinks tournament from group
            unlinkTournament: function(groupId, tournamentId) {
                axios.post('/api/tournament-groups/' + groupId + '/unlink/' + tournamentId)
                        .then(function(response) {
                            toastr.info('Tournament unlinked successfully.', '', {timeOut: 2000});
                            tournamentGroups.loadGroup(tournamentGroups.selectedGroup.id);
                            tournamentGroups.loadMyGroups();
                        }, function(response) {
                            // error handling
                            toastr.error('Something went wrong.', '', {timeOut: 2000});
                        }
                );
            }
        }

    });
</script>