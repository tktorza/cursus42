import { Component } from "@angular/core";

@Component({
    selector: "loader",
    templateUrl: `client/components/shared/loader/loader.component.html`
})
export class LoaderComponent {
    
    private visible: boolean;

    constructor() {
        this.visible =true;
    }

    setVisible(visible: boolean): LoaderComponent{
        this.visible = visible;

        return this;
    }
}
