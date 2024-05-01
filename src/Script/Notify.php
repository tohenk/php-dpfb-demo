<?php

/*
 * The MIT License
 *
 * Copyright (c) 2021-2024 Toha <tohenk@yahoo.com>
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
use NTLAB\JS\Repository;

class Notify extends Base
{
    /**
     * {@inheritDoc}
     * @see \NTLAB\JS\Script::configure()
     */
    protected function configure()
    {
        $this->addDependencies(['JQuery.NS']);
        $this->setPosition(Repository::POSITION_MIDDLE);
    }

    /**
     * {@inheritDoc}
     * @see \NTLAB\JS\Script::getScript()
     */
    public function getScript()
    {
        $title = $this->trans('Notification');
        $close = $this->trans('Close');
        $useToast = 'true';

        return <<<EOF
$.define('notif', {
    useToast: $useToast,
    supported: function() {
        const self = this;
        if (typeof $.notif.allowed === 'undefined') {
            let allowed = false;
            // https://developer.mozilla.org/en/docs/Web/API/notification
            if (window.Notification && Notification.permission !== "denied") {
                if (Notification.permission !== "granted") {
                    Notification.requestPermission(function(permission) {
                        if (permission === "granted") {
                            allowed = true;
                        }
                    });
                } else {
                    allowed = true;
                }
            }
            $.notif.allowed = allowed;
        }
        return $.notif.allowed;
    },
    notifyNotification: function(message, options) {
        const self = this;
        if (typeof options === 'string') {
            let icon;
            switch (options) {
                case 'success':
                    // https://www.iconfinder.com/iconpack
                    icon = 'data:image/png;base64,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';
                    break;
                case 'error':
                    // https://www.iconfinder.com/iconpack
                    icon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGIAAACACAYAAADwKbyHAAAKrElEQVR4nO2d349VxR3AZwWxEk1DtS8NTWhjIw2aSldZdu+Z7yyFEBR/ATl0d+89852aeh+a8KRoRGK0PEh8kRgeDBqMPtiEPwAf/NE0IRDZ1SIGF7rsnjMzULBGrU9gFW4f7l5E4Myce37eJfNJbrIPe2e+M59z5vc5lxCHw+FwOBwOh8PhcDgcDofD4XA4HA6Hw+FwOBwOh8MxV5De2CLp8TWaiq0K8HXN8APJxKRi/AvNxDkFeEEBXtBMnFOMfyGZmNQg3tcMX1OAT0qPr5He2KKqyzHn2Of7CxTgWg1il2b801PDf2pl/SgmLmomjirAl6XH10z0N2+supw9SYuQvrBWX6mZ2KOZ+CqPyjd9NOCXCsSr00P1+1qE9FVd/sqZ6G/eKL0gkAw/Lrry4z4SxEcR8LEWGZ5fdX2UTosMz1fAH1MgZFUCrtF8hRI4tnx/XtX1UwrS42sU4LGqKz622WLiaOg1hquup8KYWu7/XDH+dtUV3YWQtyZXbLit6nrLFenxBxXD/2SolHOaicOK8b0K8FkJHCOK61WtQUMvGAi9YEDVGjSiuF4CRwW4XTF8QzM+rgHPp26uAM+GXmNd1fWXmYn+5o0axK4U7fVFScUBSfGZGcpX7PP9BWlj2Of7C0IvGFCMb5MMD6YRIhl/ac525if6m7drwH90eeX/S1Ox9Xht5BdFxTU1UF8sgT+tGZ/qSgYV7825ieHJwcYd3RRUUnEgguCBFiE3lBVji5AbpBc8pJk4lPzOEJPhypElZcWYiana6DLNxJmEbfCx0Gusq3JS1SKkL6K4XjIxmeiuBXEqHBxdWlW8iZiqjS5TjH+RrPPFp3ppqWGf7y9QjG9L0rlLEJ/3rIx2c2S/EyTDj6OVwW+rjjeOk0PBXZqJo/Y7A0/3XDN1or95e5I+QQG+/vclwz+pOl4bE/3NhQrwzQQX1Wc904G3h6j20ZECfGIuLbC1COlTjG9LMLR9tyeGtrZ5ggK8IIFj1XGmRdLgccXERUsHvrPaID3+oL1jm7sSOkgaPJ7gjl9bSXDttSPzsoUCfMKWTugFA4oGI2XEfC0iwPp0LbjX9n+2ZkozcaaS/sK2gKcZvmbrE0IvGNCA/1Ugvo8A62XF3kEC8tlt1q9tMlqE9Gkm3jJeeIzvLSt2Qkh7Kds2RLWNjjoSfrh7ypXRkXDZFW2VMdHfXGgb2kqPe6UUoL2pE7+foJk4Z5snXCmhbBlXSuhGxvRg/W4N4lvTRVjKUo0C/pilSXrK9P04CWXJiJPQjQwFuN3YRHk4WlT8hJD2nMG0vakAj5mWLWwSipYhvSAwSUgqo33SRJyI/z6fKnTLVXpBYCqAaRMlqYSiZCSVkFSGovwRY18BuDmv2H9Ei5A+ycQ/YzOm4oBplKRoMKJAfJ+0IvKU0a2ETt6nmPBN9aEZH48VCfhh1rivSegFA6bAIwgesKURAdbLlpFWggL+R2t5PL7BWCeDfHnauGPRTOwxBH4i6UihTBmS8kZREgghpOX78xTDGUPz9kq3MRvZ5/sLTCfwNBVbu0mvDBlFS+hgGkEpwLO5LggqwLWxmTFxMc0ec5EyypJACCHhypElxnRrDdptmrGYVlglFQfSpluEjDIldFBUTMS2FnmuzJpOZUuKz2RJO08Z7bTKlUAIIZLiC7EiGB/PkvYPmXhji0wFmaF8RdY88pBRlQRCCJkBhPg88MLxOx++NWsexgU+zcS5LIe/LieLjColEELIwcWDNyvGv4u9WAEhcyaaiq0GEYdzKMcl0spI9518JHTQID6Jy09SviVzBgrw9dgCFbD+nkZG1RIIMe/PKMDdmTPQDD8wZPBsDmW4iqJkFCWBEEIU4A5Dy/FO5gxMJ+CK3I/OW0aREgghRK7CZqwIyo9kzsB0ci+iuD6HMsSSl4yiJbRjDTbGigA8nTkDzcS52ALmOWuMIauMMiQQQoik9dWGGL7JnIFpWBh6wUAOZbCSVkZZEgghRHrcM9wR5zNn4EQko3ARvdE0dTdZuz6bpso76/QSrqvOuqrha5pVVKuMAk8UFj581SDejy8cbs+hDFeRt4QyZBQ+odMMX4stGMM3cijDjyhKQtEyCl/iUIBPxpvOaa19lvSbOikW/XKWUfiin3EZHPB8XsvgWU5bpD2qk9eJvIn+5kLTMnguo0vpjS0yPaiRx1wijyMvVcoIacBMeeSyMUQIIaYT0IrxbVnSzvPcUVUyLB11fns2CvDl2PaP4cG06RZx+KsKGRLER7EigL+YNt2rM7I8DzE1UF/cffDmU9lpJHRQHo6m23blY93XzdivTenm+rzEPt9foAG/jM0M+NNdBV+ghA5lydAMn4tvlvDfuZ8KVyBejc+QTyU9clmGhEsxFyyj5fvzJOVRfLMkdnUbs5Xpofp95lsweMiWRpkSOhQpI6K4yZTOjNf4Xdq4Y2kR0mfslJg4ZDqWX+WRlwj4WN6TvvZjCvEvhdRMHMoat7FApuBNq7HTteBezcTXZUu4PPZuZGgmvopo4/ex6Vnuhojiprxiv4oWGZ6vmAhjmycmJk2PbiWVUdSydVIZNgn777j/Jk3Fydh6oPx44W/LlMDRVAjbWVibjKL3DmwybBIIIUSCeN5YB0U9tnU5Ld+fZ5ppa8DzJ4eCu0xpxMkoazctbus1iYRwaOwezfB/8SL5eGlvYotqfJXl1j460d9caErjShll7i8TcrWMJBKOLfNvkQw/M/YNQ3ywrDIQQgixvg4B8E3bKyA6MsqW0KEjI4mE9muDxN8sF+CesmK/xOSKDbcpwLNZ+gtC2jJMT28WjaLBiE0CIfZ+QYM4Nb2m+dMyYr6K0Guss41AIsr/XElwOaIY/4vx7mfiYgTBHyoNUjL+kjXIOSzDJmG2Gd5RdZykRYbnSyreswbL+La59io5W3PU7hdwf8+8Yf/YoP+zJO9N1Uy8ZRtN9QLHlvm32Drmzuiwsn4hjnDlyBIN4lSi4Afrd1cdbxzh0Ng9tiHq7HBbnrxv5JdVx3tNwsHRpRLE51YZIL5VgNvzOniQB/vvuP8mCeJ502TtsovpzPTA2G+qjtlIODi6VAOethVm9qo6oSh/pAdeSb3JtHZ0xeBD9byEDuHKkSVJ37XdvsL4uKKNR8vs9Fq+Py+iuKmb3zfSjH/as81RHNIbWyQZfzdpIdtXG84owO1Fvuo5WhH8SjN8zrSKHDc66rmOOSktMjxfg9jZTYEvSaFiQlJ8YQYQDi4evDltDBP9zYUhDZgC3GHa2DI0RRcV43/tmSFqFhTg2qQ/ZXDtyuDfaRCfKMbfVoA75CpsRhBslLS+Wnrckx73JK2vjiDYKFdhUwHuUIy/Pfud2BN4CQYVpyqfMedN+8Qg35u2Usr+aCb2zNmmKAmq1qBV/gCgXQAfL30puypahNygPBzt9nd+ivxIyo9LwM1l/rxOz9Dy/XkScLMG/LDCJuhQRHHTddEZ50E0yJdrJl6x7XHkVPlnNIhdhZw7ul5okeH5qtagGsROzfh4Xg8zaiYOa+AvSo977upPwfE7H751BhAk5VsU4G7NxDua8iMa8LQC8Y0GPK8Bz8/+fVpTfkQz8Y4C3C0p36JqDZrb8wkOh8PhcDgcDofD4XA4HA6Hw+FwOBwOh8PhcDgcDoejDP4PTN+s8sGOUx4AAAAASUVORK5CYII=';
                    break;
                case 'info':
                    // https://www.iconfinder.com/yanlu
                    icon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAACsklEQVRYhe2Ty2pUQRCGezAXFzk6U9V5A4lRhKAgusxOBnfKuI5BY9TNYCDJOV2VvahvENFXEBFR9BVCTLLLE4TBxZwzl6o20i5UBC/nEg24yAe9+6v+v6urjTniiCrMheOQ6A10+hzZ7yBpZkk/W9YU2e8A+2eTa/st09wd/7fGrTCGTpct6UckedMguVNf6c2Y+U5kWuGYme9E9ZXejHWyiCxvgaQDJEtmIYz+tXcUp9Po/LYlfRHF6XSZmonl7IwleQnsN6M4nTqwObjBZWQdAA1vVq8ONUiGt5A0w3hwqXJ5FKfTyDqARK5UN/9BI5EmsvaqTaIVxtD57YPd/FeAhreB/Wbpnfi2cC+KdJb9E2QVS/5xvjLULMkrcPKg2H0uHLekH6Pl9HRhUFaZXPsUkHRYpJ2Is7NA0in8opDoDSR5U5zUGGB9hKRDZH1YRo8s78Dp9XyR0+cNkjtlGlYFndxD9k/zRex36iu9mcMIUF/tnwf2W/kBSDMz34mKmk2ufQrfT+kE7VC3pN1cjSX9bFrhWNmelQLMhhHLup8fgDUtM4EDBSgzgao7UCXAyaR/oXAHgP0z62TxMAIgyX3r/HpBw/0Wsrw9jACW5D2SXstXNXfHgaQzsZyd+ZcBGkl2Dkn2TCuMFYqBZMmSvDQm1PKMfz5/7hhqQPIanLTLhDVmIYwC+01IhrfKFeRjnSxa9htmNoyULoridApZe41Emn9j3kjkqiXtnljtnqpcjPHgErL2gIa3857j94Qaktz9at6/WNn8O1GcToHzHyzJq4k4O1umppFk54DktWW/caCb/8JCGAWSJSDpIMs7dHKvvto/b9qhbmbDiGmH+smkfwFJ7n/9arIHTtqV3rwUzd1xcHod2T8F9luWtGtZ9y1pF9hvWefXkfRaqa92xP/CF/G5d2q21MVhAAAAAElFTkSuQmCC';
                    break;
                case 'warn':
                    // https://www.iconfinder.com/yanlu
                    icon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAACR0lEQVRYhe2VP2gUQRTG5zQS4+Vy2ZkXBcEgqAGb1CksooIYEYJFCsWglSIKQkjuLjvzIoJlehEsbWyEgJUoiGAS9QoPBI0HFqYJpDC5nHfz3knGwkSiub2s96/xPthi3/v2/T5mdhghWmqpBsUmc32AnAbkt/GJlSPNpY+43WD4nTL2htKUUIbnhHC7msZX2t5SaJ/9enMRhTzrGXu9KfB9ie8HAYm7UqtHN2uen+9XSPno7fyBhgcApMfS0J1tdcPToOlRQ+HS2LOgeUEMZdu3NUeXooD0VfnF042hjyx2SOQvcVM8FWRRmoYB+bO46vbWna8M3ft7iQE5DYbf/FEzNFNui2pSZ2LtuELKd46v7d9a75kquZ6pktta85KuF5B+dE2sHqsT3kUA+WW5Y1YugBBCgLbjCu1zIVykZrzSfEUhz5YbFhRADLo2qTnj+XSpJnjsZk4B0orn5/vL9QMDCCGkLgxIY5fjKedVHQA0PwTD00H9SgGEEAKQHyjk+1XBPV08AYYWxehStNoA8ZTzpLHLUhcG/o1+ze1RyB+UpuFKNoU8Lw2/rujx6bJEfi8GXVtovjKUBEMzoT/YQWDsC6ntWChzd+rbYUBa95Kut14BYpO5PjBU6hgrHNrB6iKA9iloOx5mMCCnFfJ8OC/dVUhPKpqUpgtScybsfoX5B35rKNuuNGc9Y88HDzQ81+0XT4YaWIU2LqtXgQZA/uQZe64xeBfxNF2UmjOBFmnsGdC8sHm+6/kA0jpo/ljpOm+ppf9PPwHiDETAsgiBMAAAAABJRU5ErkJggg==';
                    break;
            }
            options = {};
            if (icon) {
                options.icon = icon;
            }
        }
        const title = options.title || '$title';
        const content = {body: message};
        if (options.icon) {
            content.icon = options.icon;
        }
        const n = new Notification(title, content);
        setTimeout(n.close.bind(n), options.delay || 5000);
        return n; 
    },
    notifyToast: function(message, options) {
        const self = this;
        let icon;
        switch (options) {
            case 'success':
                icon = 'bi-check-circle text-success';
                break;
            case 'error':
                icon = 'bi-x-circle text-danger';
                break;
            case 'info':
                icon = 'bi-info-circle text-info';
                break;
            case 'warn':
                icon = 'bi-exclamation-circle text-warning';
                break;
        }
        const tmpl =
            '<div class="toast" role="alert" aria-live="assertive" aria-atomic="true">' +
              '<div class="toast-header">' +
                '<span class="bi-bell me-1"></span>' +
                '<strong class="me-auto">%TITLE%</strong>' +
                '<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="$close"></button>' +
              '</div>' +
              '<div class="toast-body">' +
                '<div class="d-flex align-items-center">' +
                  '<div class="flex-shrink-0"><span class="%ICON% fs-4"></span></div>' +
                  '<div class="flex-grow-1 ms-3">%MESSAGE%</div>' +
                '</div>' +
              '</div>' +
            '</div>';
        const toast = tmpl
            .replace(/%TITLE%/, options.title || '$title')
            .replace(/%ICON%/, icon)
            .replace(/%MESSAGE%/, message)
        ;
        if (typeof self.container === 'undefined') {
            self.container = $('<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>').appendTo(document.body);
        }
        const el = $(toast).appendTo(self.container);
        const t = new bootstrap.Toast(el[0]);
        t.show();
        return t;
    },
    notify: function(message, options) {
        const self = this;
        if (!self.useToast && self.supported()) {
            self.notifyNotification(message, options);
        } else {
            self.notifyToast(message, options);
        }
    },
    init: function() {
        const self = this;
        if (typeof $.notify === 'undefined') {
            $.notify = self.notify.bind(self);
        }
    }
});
$.notif.init();
EOF;
    }
}