import { Component, OnInit } from "@angular/core";

declare var $;

@Component({
    selector: "headerMenu",
    templateUrl: `client/components/shared/header/header.component.html`
})
export class HeaderComponent implements OnInit {
    
    constructor() {
    }

    ngOnInit() {
        this.setListeners();
    }

    setListeners() {
        $('.hamburger').click(function(){
            if ($('body').hasClass('menu-left-opened')) {
                $(this).removeClass('is-active');
                $('body').removeClass('menu-left-opened');
                $('html').css('overflow','auto');
            } else {
                $(this).addClass('is-active');
                $('body').addClass('menu-left-opened');
                $('html').css('overflow','hidden');
            }
        });

        $('.mobile-menu-left-overlay').click(function(){
            $('.hamburger').removeClass('is-active');
            $('body').removeClass('menu-left-opened');
            $('html').css('overflow','auto');
        });

        // Right mobile menu
        $('.site-header .burger-right').click(function(){
            if ($('body').hasClass('menu-right-opened')) {
                $('body').removeClass('menu-right-opened');
                $('html').css('overflow','auto');
            } else {
                $('.hamburger').removeClass('is-active');
                $('body').removeClass('menu-left-opened');
                $('body').addClass('menu-right-opened');
                $('html').css('overflow','hidden');
            }
        });

        $('.mobile-menu-right-overlay').click(function(){
            $('body').removeClass('menu-right-opened');
            $('html').css('overflow','auto');
        });
    }
}
