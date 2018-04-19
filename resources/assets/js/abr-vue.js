// confirm button
Vue.component('confirm-button', {
    props: ['buttonText', 'buttonClass', 'buttonIcon'],
    template: `
        <button type="button" :class="buttonClass" data-toggle="modal" data-target="#modal-confirm" @click="$emit('click');">
            <i :class="buttonIcon" aria-hidden="true" v-if="buttonIcon"></i>
            {{ buttonText }}
        </button>
    `
});

// confirm modal
Vue.component('confirm-modal', {
    props: {
        modalBody: { type: String, default: ''},
        callback: { type: Function, default() {}}
    },
    template: `
        <div class="modal fade" id="modal-confirm" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h5 class="modal-title" id="exampleModalLabel">Please confirm</h5>
                    </div>
                    <div class="modal-body">
                        {{ modalBody }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" @click="callback" data-dismiss="modal">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    `
});


