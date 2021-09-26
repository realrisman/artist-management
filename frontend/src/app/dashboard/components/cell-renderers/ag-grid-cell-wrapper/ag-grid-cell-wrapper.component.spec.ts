import {async, ComponentFixture, TestBed} from '@angular/core/testing';

import {AgGridCellWrapperComponent} from './ag-grid-cell-wrapper.component';

describe('AgGridCellWrapperComponent', () => {
    let component: AgGridCellWrapperComponent;
    let fixture: ComponentFixture<AgGridCellWrapperComponent>;

    beforeEach(async(() => {
        TestBed.configureTestingModule({
            declarations: [AgGridCellWrapperComponent]
        })
            .compileComponents();
    }));

    beforeEach(() => {
        fixture   = TestBed.createComponent(AgGridCellWrapperComponent);
        component = fixture.componentInstance;
        fixture.detectChanges();
    });

    it('should create', () => {
        expect(component).toBeTruthy();
    });
});
