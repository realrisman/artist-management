import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { CelebritiyListComponent } from './celebrity-list.component';

describe('CelebritiesComponent', () => {
  let component: CelebritiyListComponent;
  let fixture: ComponentFixture<CelebritiyListComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ CelebritiyListComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(CelebritiyListComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
