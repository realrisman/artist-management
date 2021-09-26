import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CelebrityQuickViewCellRendererComponent } from './celebrity-quick-view-cell-renderer.component';

describe('CelebrityQuickViewCellRendererComponent', () => {
  let component: CelebrityQuickViewCellRendererComponent;
  let fixture: ComponentFixture<CelebrityQuickViewCellRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CelebrityQuickViewCellRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CelebrityQuickViewCellRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
