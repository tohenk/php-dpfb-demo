<?php

/*
 * The MIT License
 *
 * Copyright (c) 2021 Toha <tohenk@yahoo.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Demo\Script;

use NTLAB\JS\Script\JQuery as Base;

class Fingerprint extends Base
{
    /**
     * {@inheritDoc}
     * @see \NTLAB\JS\Script\JQuery::initialize()
     */
    protected function initialize()
    {
        $this->addDependencies(['JQuery.NS', 'JQuery.Util', 'Bootstrap.Dialog', 'SocketIO', 'Notify']);
    }

    /**
     * {@inheritDoc}
     * @see \NTLAB\JS\Script::getScript()
     */
    public function getScript()
    {
        $with_sample          = 'false';
        $enroll               = $this->trans('Finger Enrollment');
        $close                = $this->trans('Close');
        $finger_thumb         = $this->trans('Thumb Finger');
        $finger_index         = $this->trans('Index Finger');
        $finger_middle        = $this->trans('Middle Finger');
        $finger_ring          = $this->trans('Ring Finger');
        $finger_litle         = $this->trans('Little Finger');
        $left_finger          = $this->trans('Left Finger');
        $right_finger         = $this->trans('Right Finger');
        $left_finger_format   = $this->trans('Left %FINGER%');
        $right_finger_format  = $this->trans('Right %FINGER%');
        $choose_finger        = $this->trans('Select finger to enroll, to speed up start from %START% and end with %END%.');
        $enroll_finger        = $this->trans('Enroll your %FINGER%');
        $swipe                = $this->trans('Swipe your finger');
        $swipe_multiple       = $this->trans('Swipe your finger %NR% time(s)');
        $swipe_again          = $this->trans('Swipe your finger again');
        $connect              = $this->trans('Connect fingerprint device');
        $enroll_complete      = $this->trans('Finger enrollment complete');
        $unenroll             = $this->trans('Finger Unenrollment');
        $unregister           = $this->trans('Unregister');
        $unenroll_finger      = $this->trans('Check all fingers to unenroll, choose Unregister when done.');
        $verify               = $this->trans('Finger Verification');
        $verify_wait          = $this->trans('Please wait, your finger is being verified...');
        $verify_nomatch       = $this->trans('Nothing matched, try again...');
        return <<<EOF
$.define('fp', {
    url: 'http://localhost:7879',
    connected: null,
    sample: $with_sample,
    callback: null,
    fingers: {
        RIGHT_THUMB:
          { index: 1, name: '$finger_thumb', fmt: '$right_finger_format' },
        RIGHT_INDEX_FINGER:
          { index: 2, name: '$finger_index', fmt: '$right_finger_format' },
        RIGHT_MIDDLE_FINGER:
          { index: 3, name: '$finger_middle', fmt: '$right_finger_format' },
        RIGHT_RING_FINGER:
          { index: 4, name: '$finger_ring', fmt: '$right_finger_format' },
        RIGHT_LITTLE_FINGER:
          { index: 5, name: '$finger_litle', fmt: '$right_finger_format' },
        LEFT_THUMB:
          { index: 6, name: '$finger_thumb', fmt: '$left_finger_format' },
        LEFT_INDEX_FINGER:
          { index: 7, name: '$finger_index', fmt: '$left_finger_format' },
        LEFT_MIDDLE_FINGER:
          { index: 8, name: '$finger_middle', fmt: '$left_finger_format' },
        LEFT_RING_FINGER:
          { index: 9, name: '$finger_ring', fmt: '$left_finger_format' },
        LEFT_LITTLE_FINGER:
          { index: 10, name: '$finger_litle', fmt: '$left_finger_format' }
    },
    fingerTmpl: {
        enroll: '<a href="#" class="btn btn-outline-primary btn-sm mb-1" data-index="%INDEX%" role="button"><span class="fas fa-fingerprint"></span> %NAME%</a>',
        unenroll:
            '<div class="form-check custom-control custom-checkbox">' +
              '<input type="checkbox" class="custom-control-input" id="finger-check-%INDEX%" data-index="%INDEX%"/>' +
              '<label for="finger-check-%INDEX%" class="custom-control-label">%NAME%</label>' +
            '</div>'
    },
    isFinger: function(index) {
        var self = this;
        return index >= self.fingers.RIGHT_THUMB.index &&
            index <= self.fingers.LEFT_LITTLE_FINGER.index ? true : false;
    },
    fingerValue: function(index) {
        var self = this;
        var value = 0;
        if (self.isFinger(index)) {
            value = 1 << (index - 1);
        }
        return value;
    },
    includeFinger: function(mask, index) {
        var self = this;
        return mask | self.fingerValue(index);
    },
    excludeFinger: function(mask, index) {
        var self = this;
        return mask & ~(self.fingerValue(index));
    },
    hasFinger: function(mask, index) {
        var self = this;
        var value = self.fingerValue(index);
        if (value > 0) {
            return (mask & value) == value ? true : false;
        }
    },
    getNextUnenrolledFinger: function(mask) {
        var self = this;
        var index = 0;
        for (var i = self.fingers.RIGHT_THUMB.index; i <= self.fingers.LEFT_LITTLE_FINGER.index; i++) {
            if (!self.hasFinger(mask, i)) {
                index = i;
                break;
            }
        }
        return index;
    },
    getFingerId: function(index) {
        var self = this;
        for (var key in self.fingers) {
            if (self.fingers[key].index == index) {
                return key;
            }
        }
    },
    getFingerInfo: function(id, type) {
        var self = this;
        switch (type) {
            case 0:
            case 'index':
                return self.fingers[id] ? self.fingers[id].index : null;
            case 1:
            case 'name':
                return self.fingers[id] ? self.fingers[id].name : null;
            case 2:
            case 'fullname':
                return self.fingers[id] ? self.fingers[id].fmt.replace(/%FINGER%/, self.fingers[id].name) : null;
        }
    },
    getFingerButton: function(index, template) {
        var self = this;
        return $.util.template(template, {INDEX: index, NAME: self.getFingerInfo(self.getFingerId(index), 1)});
    },
    getFingerLayout: function(title, template) {
        var self = this;
        var rightFinger = '', leftFinger = '';
        for (var i = self.fingers.RIGHT_THUMB.index; i <= self.fingers.RIGHT_LITTLE_FINGER.index; i++) {
            rightFinger += self.getFingerButton(i, template);
        }
        for (var i = self.fingers.LEFT_THUMB.index; i <= self.fingers.LEFT_LITTLE_FINGER.index; i++) {
            leftFinger += self.getFingerButton(i, template);
        }
        return $.util.template(
            '<div class="finger-selector">' +
              '<p class="finger-selector-help mb-3">%TITLE%</p>' +
              '<div class="row px-4">' +
                '<div class="col-md-6 d-flex flex-column">' +
                  '<p class="h6">$left_finger</p>' +
                  '%LEFT%' +
                '</div>' +
                '<div class="col-md-6 d-flex flex-column">' +
                  '<p class="h6">$right_finger</p>' +
                  '%RIGHT%' +
                '</div>' +
              '</div>' +
            '</div>',
            {TITLE: title, LEFT: leftFinger, RIGHT: rightFinger}
        );
    },
    getEnrollDlg: function(create) {
        var self = this;
        if (!self.enrollDlg && create) {
            var title = '$choose_finger'.replace(/%START%/, self.getFingerInfo(self.getFingerId(self.fingers.RIGHT_THUMB.index), 2))
                .replace(/%END%/, self.getFingerInfo(self.getFingerId(self.fingers.LEFT_LITTLE_FINGER.index), 2));
            var content = self.getFingerLayout(title, self.fingerTmpl.enroll);
            content +=
                '<div class="finger-op">' +
                  '<p class="finger-op-help mb-3"></p>' +
                  '<div class="d-flex align-items-center pb-3 px-4">' +
                    '<div class="icon mr-3"><i class="fas fa-fingerprint fa-fw fa-3x"></i></div>' +
                    '<div class="msg"></div>' +
                  '</div>' +
                '</div>';
            self.enrollDlg = $.ntdlg.create('fp-enroll-dialog', '$enroll', content, {
                backdrop: 'static',
                buttons: {
                    '$close': {
                        icon: $.ntdlg.BTN_ICON_CANCEL,
                        handler: function() {
                            $.ntdlg.close($(this));
                            if (self.connected) {
                                self.socket.emit('stop');
                            }
                        }
                    }
                }
            });
            self.enrollDlg.find('.finger-selector a').on('click', function(e) {
                e.preventDefault();
                self.enrollFinger(parseInt($(this).attr('data-index')));
            });
        }
    },
    updateEnrolledFingerMask: function() {
        var self = this;
        for (var i = self.fingers.RIGHT_THUMB.index; i <= self.fingers.LEFT_LITTLE_FINGER.index; i++) {
            var enabled = !self.hasFinger(self.fingerMask, i);
            if (enabled) {
                self.enrollDlg.find('.finger-selector a[data-index=' + i + ']')
                    .removeClass('btn-outline-secondary')
                    .addClass('btn-outline-primary')
                    .removeClass('disabled')
                ;
            } else {
                self.enrollDlg.find('.finger-selector a[data-index=' + i + ']')
                    .removeClass('btn-outline-primary')
                    .addClass('btn-outline-secondary')
                    .addClass('disabled')
                ;
            }
        }
    },
    enrollFinger: function(index) {
        var self = this;
        self.fingerIndex = index;
        self.enrollCount = self.featuresLen;
        self.enrollDlg.find('.finger-selector').addClass('d-none');
        self.enrollDlg.find('.finger-op').removeClass('d-none');
        self.enrollDlg.find('.finger-op-help').html('$enroll_finger'.replace(/%FINGER%/, self.getFingerInfo(self.getFingerId(self.fingerIndex), 2)));
        self.socket.emit('enroll');
    },
    enroll: function(mask, index) {
        var self = this;
        if (self.connected) {
            self.fingerMask = mask;
            self.getEnrollDlg(true);
            self.enrollDlg.find('.finger-selector').removeClass('d-none');
            self.enrollDlg.find('.finger-op').addClass('d-none');
            self.updateEnrolledFingerMask();
            $.ntdlg.show(self.enrollDlg);
            if (undefined != index) {
                self.enrollFinger(index);
            }
        }
    },
    getUnenrollDlg: function(create) {
        var self = this;
        if (!self.unenrollDlg && create) {
            var title = '$unenroll_finger';
            var content = self.getFingerLayout(title, self.fingerTmpl.unenroll);
            self.unenrollDlg = $.ntdlg.create('fp-unenroll-dialog', '$unenroll', content, {
                backdrop: 'static',
                buttons: {
                    '$unregister': {
                        icon: $.ntdlg.BTN_ICON_OK,
                        handler: function() {
                            $.ntdlg.close($(this));
                            var fingers = [];
                            self.unenrollDlg.find('input:checked').not(':disabled').each(function() {
                                fingers.push(parseInt($(this).attr('data-index')));
                            });
                            if (fingers.length) {
                                if (typeof self.callback == 'function') {
                                    self.callback('finger-unenrolled', fingers);
                                }
                            }
                        }
                    },
                    '$close': {
                        icon: $.ntdlg.BTN_ICON_CANCEL,
                        handler: function() {
                            $.ntdlg.close($(this));
                        }
                    }
                }
            });
        }
    },
    updateUnenrolledFingerMask: function() {
        var self = this;
        for (var i = self.fingers.RIGHT_THUMB.index; i <= self.fingers.LEFT_LITTLE_FINGER.index; i++) {
            var enabled = self.hasFinger(self.fingerMask, i);
            if (enabled) {
                self.unenrollDlg.find('.finger-selector input[data-index=' + i + ']').prop('disabled', false);
            } else {
                self.unenrollDlg.find('.finger-selector input[data-index=' + i + ']').prop('disabled', true);
            }
        }
        self.unenrollDlg.find('.finger-selector input[type=checkbox]').prop('checked', false);
    },
    unenroll: function(mask) {
        var self = this;
        self.fingerMask = mask;
        self.getUnenrollDlg(true);
        self.updateUnenrolledFingerMask();
        $.ntdlg.show(self.unenrollDlg);
    },
    getVerifyDlg: function(create) {
        var self = this;
        if (!self.verifyDlg && create) {
            var content =
                '<div class="finger-op">' +
                  '<div class="d-flex align-items-center pb-3 px-4">' +
                    '<div class="icon mr-3"><i class="fas fa-fingerprint fa-fw fa-3x"></i></div>' +
                    '<div class="msg"></div>' +
                  '</div>' +
                '</div>';
            self.verifyDlg = $.ntdlg.create('fp-verify-dialog', '$verify', content, {
                backdrop: 'static',
                buttons: {
                    '$close': {
                        icon: $.ntdlg.BTN_ICON_CANCEL,
                        handler: function() {
                            $.ntdlg.close($(this));
                            self.stopVerifyTimer();
                            if (self.connected) {
                                self.socket.emit('stop');
                            }
                        }
                    }
                }
            });
        }
    },
    startVerifyTimer: function() {
        var self = this;
        if (self.verifyDlg) {
            self.verifyDlg.find('.finger-op .msg').html('<div>$verify_wait</div><div class="h6 elapsed"></div>');
            self.verifyDlg.find('.finger-op .icon').addClass('text-success');
            self.buildTimer();
        }
    },
    stopVerifyTimer: function() {
        var self = this;
        if (self.timer) {
            clearInterval(self.timer);
            self.timer = null;
            self.verifyDlg.find('.finger-op .icon').removeClass('text-success');
        }
    },
    buildTimer: function() {
        var self = this;
        self.start = new Date().getTime();
        var f = function() {
            var elapsed = Math.round((new Date().getTime() - self.start) / 1000);
            self.verifyDlg.find('.elapsed').text(self.formatTime(elapsed));
        }
        f();
        self.timer = setInterval(f, 1000);
    },
    formatTime: function(seconds) {
        var self = this;
        if (!self.seconds_in_hour) self.seconds_in_hour = 60 * 60;
        if (!self.seconds_in_minute) self.seconds_in_minute = 60;
        
        var hour = Math.floor(seconds / self.seconds_in_hour);
        var seconds = seconds - (hour * self.seconds_in_hour);
        var minute = Math.floor(seconds / self.seconds_in_minute);
        var seconds = seconds - (minute * self.seconds_in_minute);
        var second = seconds;
        
        return self.formatTick(hour) + ':' + self.formatTick(minute) + ':' + self.formatTick(second);
    },
    formatTick: function(value) {
        var value = value.toString();
        while (value.length < 2) {
            var value = '0' + value;
        }
        
        return value;
    },
    closeVerifyDialog: function() {
        var self = this;
        if (self.verifyDlg && self.verifyDlg.hasClass('modal')) {
            $.ntdlg.close(self.verifyDlg);
        }
    },
    retryAcquire: function() {
        var self = this;
        if (self.verifyDlg && self.connected) {
            self.verifyDlg.find('.finger-op .msg').html('<div class="text-danger">$verify_nomatch</div>');
            self.verifyDlg.find('.finger-op .icon').addClass('text-danger');
            setTimeout(function() {
                self.verifyDlg.find('.finger-op .icon').removeClass('text-danger');
                self.startAcquire();
            }, 2000);
        }
    },
    startAcquire: function() {
        var self = this;
        if (self.connected) {
            self.socket.emit('acquire');
        }
    },
    acquire: function() {
        var self = this;
        if (self.connected) {
            self.getVerifyDlg(true);
            if (self.verifyDlg.hasClass('modal')) {
                $.ntdlg.show(self.verifyDlg);
            }
            self.startAcquire();
        }
    },
    init: function(callback) {
        var self = this;
        self.callback = callback;
        self.socket = io.connect(self.url, {reconnect: true});
        var notifyConnection = function() {
            $(document).trigger('fpconnect');
        }
        self.socket.on('connect', function() {
            self.socket.emit('self-test');
        });
        self.socket.on('disconnect', function() {
            self.connected = false;
            notifyConnection();
        });
        self.socket.on('self-test', function(response) {
            if (response) {
                var svrName = response.substr(0, response.indexOf('-'));
                var svrVer = response.substr(response.indexOf('-') + 1);
                if (svrName == 'DPFPBRIDGE') {
                    self.connected = true;
                    self.server = {name: svrName, protocol: svrVer};
                    console.log(self.server);
                    self.socket.emit('required-features');
                    self.socket.emit('set-options', {enrollWithSamples: self.sample});
                    notifyConnection();
                }
            }
        });
        self.socket.on('required-features', function(featuresLen) {
            self.featuresLen = featuresLen;
            console.log('Features length = %d', featuresLen);
        });
        self.socket.on('acquire-status', function(data) {
            console.log('Acquire status = %s', data.status);
            if (self.verifyDlg) {
                var msg;
                switch (data.status) {
                    case 'connected':
                        msg = '$swipe';
                        break;
                    case 'disconnected':
                        msg = '$connect';
                        break;
                }
                if (msg) {
                    self.verifyDlg.find('.finger-op .msg').html(msg);
                }
            }
        });
        self.socket.on('acquire-complete', function(data) {
            console.log('Acquire complete = %s', data.data);
            if (self.verifyDlg) {
                if (typeof self.callback == 'function') {
                    self.callback('finger-acquired', data.data);
                }
            }
        });
        self.socket.on('enroll-status', function(data) {
            console.log('Enroll status = %s', data.status);
            if (self.enrollDlg) {
                var msg;
                switch (data.status) {
                    case 'connected':
                        msg = '$swipe_multiple'.replace(/%NR%/, self.featuresLen);
                        break;
                    case 'disconnected':
                        msg = '$connect';
                        break;
                }
                if (msg) {
                    self.enrollDlg.find('.finger-op .msg').html(msg);
                }
            }
        });
        self.socket.on('enroll-complete', function(data) {
            console.log('Enroll complete = %s', data.data);
            if (self.enrollDlg) {
                self.enrollDlg.find('.finger-op .icon').addClass('text-success');
                setTimeout(function() {
                    self.enrollDlg.find('.finger-op .icon').removeClass('text-success');
                }, 500);
                if (self.enrollCount > 0) self.enrollCount--;
                if (self.enrollCount > 0) {
                    self.enrollDlg.find('.finger-op .msg').html('$swipe_multiple'.replace(/%NR%/, self.enrollCount));
                } else {
                    self.enrollDlg.find('.finger-op .msg').html('$swipe_again');
                }
            }
        });
        self.socket.on('enroll-finished', function(data) {
            console.log('Enroll finished = %s', data.template);
            if (self.enrollDlg) {
                self.enrollDlg.find('.finger-op .msg').html('$enroll_complete');
                $.ntdlg.close(self.enrollDlg);
                if (typeof self.callback == 'function') {
                    self.callback('finger-enrolled', data.template);
                }
            }
        });
    }
});
EOF;
    }
}