import { Component, OnInit, ViewChild } from '@angular/core';

import { ApiService } from "../../service/api/api.service";
import { OrderService } from '../../service/order/order.service';

import { LoaderComponent } from "../../components/shared/loader/loader.component";

import { Order } from '../../models/order.model';

declare var $;

@Component({
    selector: 'dashboard',
    templateUrl: 'client/modules/dashboard/dashboard.component.html'
})
export class DashBoardComponent implements OnInit {
    @ViewChild('loader') loader: LoaderComponent;

    private orders: Order[];
    private table: any;
    private loading: boolean;
    private  preparationStates: any;
    public  initTable: any;
    public  lines: any;
    public  form: any;
    public inputs: any;

    constructor(private apiService: ApiService, private orderService: OrderService) {
        this.orders = [];
        this.loading = true;
        this. preparationStates = [
            { key:0, value:"En attente" },
            { key:1, value:"En Preparation" },
            { key:2, value:"En livraison" },
            { key:3, value:"Delivrée" },
            { key:4, value:"Probleme de livraison" }
        ];

        this.inputs = [
                {
                    state: "input",
                    label: 'Pseudo',
                    type: 'text',
                    required: true,
                    id: 'test',
                    placeholder: 'Booba4000',
                    model: ''
                },
                {
                    state: "input",
                    label: 'Mot de passe',
                    type: 'password',
                    required: true,
                    id: 'test2',
                    placeholder: '',
                },
                {
                    options: ['1995', '1996', '1997', '1998', '2000'],
                    state: "select",
                    label: 'annee de naissance',
                    type: 'text',
                    required: true,
                    id: 'test',
                    placeholder: '',
                    model: '1997'
                },
                {
                    state: "input",
                    label: 'email',
                    type: 'ok',
                    required: false,
                    id: 'test3',
                    placeholder: 'sushi@click-eat.fr',
                    model: ''
                }
            ];

        this.form = {
            url: '../tipe.html',
            title: "Hello",
            submit: 
                {
                    class: ""
                }
            };

        this.initTable = [
            {
            'id': 'test1',
            'class': ''
            },
            {
                id: 'check',
                class: ''
            },
            {
                id: 'text',
                class: 'state-400'
            },
            {
                id: 'link',
                class: ''
            },
            {
                id: 'stockExchange'
            },
            {
                id: 'progress',
            }
            
        ];
        this.initTable.classOfLine= {
            '0': 'state-400'
        };
        this.initTable.titleClass = "text-center";
        this.initTable.title = 'Hello world';
        this.initTable.class = '';
        this.lines = [
            [
                {
                    type: 'check',
                    status: null
                },
                {
                    type: 'link',
                    status: {
                        link: 'www.google.com',
                        text: 'google'
                    }
                },
                {
                    type: 'text',
                    status: {
                       text: 'Hello world',
                       info: 'www.info.com',
                       icon: {
                           image: '',
                           class: 'font-icon font-icon-comment'
                       }
                    }
                },
                {
                    type: 'stockExchange',
                    status: {
                        status: 'up',
                        value: '12.1'
                    }
                },
                {
                    type: 'progress',
                    status: {
                        progress: 1,
                    }
                }
            ],
            [
                {
                    type: 'check'
                },
                {
                    type: 'link',
                    status: {
                        link: 'www.amazon.com',
                        text: 'amazon'
                    }
                },
                {
                    type: 'text',
                    status: 'Hello world'
                },
                {
                    type: 'stockExchange',
                    status: {
                        status: 'down',
                        value: '12.1'
                    }
                },
                {
                    type: 'progress',
                    status: {
                        progress: 2,
                    }
                }
            ],
            [
                {
                    type: 'check',
                    status: null
                },
                {
                    type: 'link',
                    status: {
                        link: 'www.google.com',
                        text: 'google',
                        info: 'ddwdwe'
                    }
                },
                {
                    type: 'text',
                    status: {
                       text: 'Hello world',
                       info: 'www.info.com'
                    }
                },
                {
                    type: 'stockExchange',
                    status: {
                        status: 'up',
                        value: '12.1',
                        info: 'ddwdwe'               
                    }
                },
                {
                    type: 'progress',
                    status: {
                        progress: 3,
                    }
                }
            ]
        ];
    }

    onChange(event: any) {
        console.log(event);
    }

    ngOnInit() { 
        let self = this;
        this.apiService.setToken('luiz@click-eat.fr', '1234').subscribe(
            ()=>{
                let headers = self.apiService.getHeaders();
                console.log(headers);
                self.orderService
                    .setHeaders(headers)
                    .getRestaurantOrders(1, {start: "2016-09-01", end: "2016-09-30"})
                    .subscribe((orders) => {
                        self.orders = orders;
                        self.loading = false;
                        self.loader.setVisible(false);
                        setTimeout(function(){
                            self.table = $("#datatable").DataTable({
                                responsive: true,
                                dom: 'lfrtBp',
                                buttons: [
                                    'copy', 'csv', 'excel', 'pdf', 'print'
                                ],
                                aaSorting: [],
                                "pageLength": -1,
                                "lengthMenu": [[-1, 25, 50, 10], [ "Tous", 25, 50,10]],
                                "language": {
                                    "sProcessing":     "Traitement en cours...",
                                    "sSearch":         "Rechercher&nbsp;:",
                                    "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments &nbsp;&nbsp;&nbsp;",
                                    "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
                                    "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment&nbsp;&nbsp;&nbsp;",
                                    "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
                                    "sInfoPostFix":    "",
                                    "sLoadingRecords": "Chargement en cours...",
                                    "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
                                    "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
                                    "oPaginate": {
                                        "sFirst":      "Premier",
                                        "sPrevious":   "Pr&eacute;c&eacute;dent",
                                        "sNext":       "Suivant",
                                        "sLast":       "Dernier"
                                    },
                                    "oAria": {
                                        "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
                                        "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
                                    }
                                }
                            });

                            $('.data-filter').on( 'click', function (e) {                        
                                // Get the column API object
                                var column = self.table.column( $(this).parent().attr('data-column') );
                        
                                // Toggle the visibility
                                column.visible( ! column.visible() );
                            } );

                        },100);                          
                        
                    });
            })
        ;
    }

}