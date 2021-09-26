import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CelebrityDeleteCellRendererComponent } from './celebrity-delete-cell-renderer.component';

describe('CelebrityDeleteCellRendererComponent', () => {
  let component: CelebrityDeleteCellRendererComponent;
  let fixture: ComponentFixture<CelebrityDeleteCellRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CelebrityDeleteCellRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CelebrityDeleteCellRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
