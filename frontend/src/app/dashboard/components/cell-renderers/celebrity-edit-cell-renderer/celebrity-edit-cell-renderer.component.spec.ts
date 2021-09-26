import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CelebrityEditCellRendererComponent } from './celebrity-edit-cell-renderer.component';

describe('CelebrityEditCellRendererComponent', () => {
  let component: CelebrityEditCellRendererComponent;
  let fixture: ComponentFixture<CelebrityEditCellRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CelebrityEditCellRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CelebrityEditCellRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
