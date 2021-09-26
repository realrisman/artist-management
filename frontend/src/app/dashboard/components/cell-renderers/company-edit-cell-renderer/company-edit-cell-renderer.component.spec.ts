import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CompanyEditCellRendererComponent } from './company-edit-cell-renderer.component';

describe('CompanyEditCellRendererComponent', () => {
  let component: CompanyEditCellRendererComponent;
  let fixture: ComponentFixture<CompanyEditCellRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CompanyEditCellRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CompanyEditCellRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
