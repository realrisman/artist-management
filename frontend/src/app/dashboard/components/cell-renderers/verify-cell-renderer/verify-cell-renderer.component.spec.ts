import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { VerifyCellRendererComponent } from './verify-cell-renderer.component';

describe('VerifyCellRendererComponent', () => {
  let component: VerifyCellRendererComponent;
  let fixture: ComponentFixture<VerifyCellRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ VerifyCellRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(VerifyCellRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
