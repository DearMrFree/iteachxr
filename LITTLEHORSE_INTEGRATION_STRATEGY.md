# LittleHorse Integration Strategy for Self-Driving School Ecosystem

## Executive Summary

This document outlines the integration of LittleHorse (a Business-as-Code Workflow Engine) into the iTeachXR self-driving school ecosystem. The integration enables autonomous agents and humans to collaborate seamlessly through:

1. **Workflow Orchestration**: Defining school processes as executable code
2. **Agent Autonomy**: Agents automatically managing curriculum, assessments, and student progression
3. **Human Oversight**: Teachers/admins maintaining control through event-based interventions
4. **Self-Testing & Correction**: The system validates itself and corrects workflows at runtime
5. **Intelligent API Generation**: APIs auto-generated from workflow specifications

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    iTeachXR Platform                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌──────────────────────┐         ┌──────────────────────┐     │
│  │  Student Learning    │         │  Teacher Dashboard   │     │
│  │  Interface (VR/XR)   │         │  (Oversight/Config)  │     │
│  └──────────────────────┘         └──────────────────────┘     │
│            │                               │                     │
│            └───────────────┬───────────────┘                     │
│                            │                                     │
│           ┌────────────────▼────────────────┐                   │
│           │  LittleHorse Workflow Engine    │                   │
│           │  (Business-as-Code Center)      │                   │
│           └─┬──────────────┬────────────────┘                   │
│             │              │                                     │
│    ┌────────▼────┐  ┌──────▼────────┐                          │
│    │ Agent Tasks │  │ Event Handler │                          │
│    │ (WfSpecs)   │  │ (Human Input) │                          │
│    └─────────────┘  └───────────────┘                          │
│             │              │                                     │
│    ┌────────┴──────────────┴────────┐                          │
│    │  Kafka Event Stream             │                          │
│    │  (Real-time Coordination)       │                          │
│    └────────┬──────────────┬────────┘                          │
│             │              │                                     │
│  ┌──────────▼──┐  ┌───────▼──────┐  ┌────────────────┐        │
│  │  Curriculum │  │  Assessment  │  │ Student State  │        │
│  │  Engine     │  │  Engine      │  │ Management     │        │
│  └─────────────┘  └──────────────┘  └────────────────┘        │
│             │              │              │                     │
│             └──────────────┴──────────────┘                     │
│                            │                                     │
│           ┌────────────────▼────────────────┐                   │
│           │  Persistent State Store         │                   │
│           │  (PostgreSQL + RocksDB)         │                   │
│           └────────────────────────────────┘                    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## Integration Components

### 1. **Core LittleHorse Components to Deploy**

```yaml
LittleHorse Server:
  - Role: Central orchestration engine
  - Deployment: Docker container or Kubernetes cluster
  - Configuration: Apache Kafka + gRPC
  - API: REST + gRPC endpoints

LittleHorse Dashboard:
  - Role: Visual monitoring of workflows
  - Features: Workflow visualization, task monitoring, error tracking
  - Integration: Teacher admin interface overlay

Workflow Definitions (WfSpecs):
  - StudentOnboardingFlow: Initial assessment and placement
  - CurriculumProgressionFlow: Adaptive learning path management
  - AssessmentFlow: Continuous evaluation with error handling
  - AgentAutoRemediationFlow: Self-correction mechanisms
  - HumanInterventionFlow: Teacher override capabilities

Task Definitions (TaskDefs):
  - EvaluateStudentLevel
  - SelectNextCourse
  - GenerateAssessment
  - EvaluateResponse
  - NotifyTeacher
  - UpdateStudentRecord
```

### 2. **Integration Points with iTeachXR**

#### A. Student Onboarding Workflow
```
WfSpec: student-onboarding
├── Input: StudentData (name, age, learning_style, prior_knowledge)
├── Task: evaluate-initial-level
│   └── Output: PlacementLevel
├── Task: select-starting-course
│   └── Based on: PlacementLevel + learning_style
├── Event: student-ready-for-learning
│   └── Correlation: student_id
├── Workflow Exit: StudentEnrolled with initial curriculum
└── Error Handling:
    ├── Timeout: Retry parent onboarding
    ├── ValidationError: Route to teacher review
    └── SystemError: Auto-remediation attempt
```

#### B. Adaptive Curriculum Progression
```
WfSpec: curriculum-progression
├── Loop (until graduation):
│   ├── Execute: current-lesson
│   ├── Task: assess-comprehension
│   ├── Decision: readiness-check
│   │   ├── If success: advance to next lesson
│   │   ├── If needs-review: replay with different approach
│   │   └── If struggle: trigger intervention-request
│   ├── Event: wait-for-human-override
│   │   └── Timeout: 24 hours (auto-retry)
│   └── Task: update-progress-record
└── Output: CompletedCurriculum
```

#### C. Self-Testing & Auto-Correction
```
WfSpec: agent-quality-assurance
├── Trigger: Every 100 workflow completions OR on error spike
├── Task: verify-workflow-outputs
│   ├── Compare: expected vs actual results
│   ├── Test: edge cases (network failure, timeout, validation)
│   └── Generate: quality metrics
├── Decision: quality-threshold-check
│   ├── Pass: Continue normal operation
│   ├── Warning: Log and monitor
│   └── Fail: Enter remediation mode
├── Task: auto-generate-fix
│   ├── Analyze: error patterns
│   ├── Suggest: code corrections
│   └── Test: corrections in sandbox
├── Decision: safe-to-deploy
│   ├── Yes: Update production WfSpec
│   ├── No: Alert engineers for review
│   └── Uncertain: Route to human reviewer
└── Event: engineer-approval-required
    └── Timeout: manual review after 8 hours
```

### 3. **API Generation & Anticipation**

#### Auto-Generated APIs
```
# Based on WfSpec definitions, auto-generate:

POST /api/workflows/{wfName}/run
  Input: WfSpec variables
  Output: WfRunId + tracking info

GET /api/workflows/{wfName}/run/{wfRunId}
  Output: Current execution state

POST /api/workflows/{wfName}/run/{wfRunId}/event/{eventName}
  Input: Event payload
  Purpose: Human intervention signal

GET /api/workflows/{wfName}/metrics
  Output: Performance metrics, success rate, avg execution time

POST /api/workflows/{wfName}/validate
  Input: Proposed WfSpec changes
  Output: Validation report + test results

GET /api/workflows/status/health
  Output: System health, queue depth, error rates
```

#### Anticipated APIs (Generated from Conversation Patterns)
```
# System learns from human interactions and generates new APIs:

POST /api/students/{studentId}/adaptive-curriculum
  # Generated because humans often ask for curriculum recommendations

GET /api/analytics/struggling-students
  # Generated because admins query for intervention targets

POST /api/assessments/{assessmentId}/regenerate
  # Generated because teachers often retry failed assessments

GET /api/agents/performance/{agentId}
  # Generated because operators monitor agent accuracy

POST /api/workflows/auto-repair/{workflowName}
  # Generated from self-correction workflow patterns
```

---

## Implementation Phases

### Phase 1: Foundation (Weeks 1-3)
```
Deliverables:
✓ LittleHorse Server deployment (Docker on dev environment)
✓ Integration with existing PostgreSQL database
✓ Apache Kafka cluster setup for event streaming
✓ Basic "Student Onboarding" WfSpec implemented
✓ Testing infrastructure in place

Code Structure:
```
iteachxr/
├── littlehorse/
│   ├── server/          # LH server configuration
│   ├── workflows/
│   │   ├── student-onboarding.py
│   │   ├── curriculum-progression.py
│   │   └── assessment.py
│   ├── tasks/
│   │   ├── task-workers.py
│   │   └── task-registry.py
│   ├── events/
│   │   ├── event-definitions.py
│   │   └── event-handlers.py
│   └── config.py
├── api/
│   └── littlehorse-bridge.py  # API layer
└── tests/
    └── littlehorse/          # Workflow tests
```

### Phase 2: Agent Autonomy (Weeks 4-6)
```
Deliverables:
✓ Curriculum progression workflow operational
✓ Agent decision-making for student path recommendations
✓ Real-time student progress tracking via Kafka
✓ Teacher intervention event endpoints
✓ Dashboard integration showing live workflows

Key Features:
- Agents autonomously assign lessons based on performance
- Teachers can intervene via event system
- System handles race conditions and distributed decisions
```

### Phase 3: Self-Testing & Correction (Weeks 7-9)
```
Deliverables:
✓ Quality assurance workflow running continuously
✓ Automated error detection and pattern analysis
✓ Sandbox environment for testing fixes
✓ Auto-deployment of safe corrections
✓ Comprehensive error logging and metrics

Testing Framework:
- Unit tests for each TaskDef
- Integration tests for WfSpec sequences
- Chaos engineering tests for failure scenarios
- A/B testing infrastructure for new workflow versions
```

### Phase 4: Intelligent API Generation (Weeks 10-12)
```
Deliverables:
✓ API generation from WfSpec metadata
✓ Conversation-based API anticipation system
✓ Self-documenting OpenAPI specifications
✓ SDK generation (Python, JavaScript, etc.)
✓ Full production-ready system

Features:
- APIs auto-generated from workflow definitions
- New endpoints anticipated from user queries
- Rate limiting and quota management
- Full audit trail and compliance logging
```

---

## Code Integration Examples

### Example 1: Student Onboarding Workflow (Python)

```python
# littlehorse/workflows/student-onboarding.py

from littlehorse.workflow import WorkflowThread, Workflow
from littlehorse.config import LHConfig
from littlehorse.model import LHErrorType

class StudentOnboardingWorkflow:
    """
    Orchestrates the complete student onboarding process.
    Agents handle most decisions, humans intervene when needed.
    """
    
    @staticmethod
    def get_workflow() -> Workflow:
        def onboarding_wf(wf: WorkflowThread):
            # Input variables
            student_id = wf.declareStr("student-id").searchable().required()
            student_data = wf.declareJson("student-data").required()
            
            # Task 1: Evaluate student's initial level
            placement_result = wf.execute(
                "evaluate-initial-level",
                student_data
            ).withRetries(3)
            
            placement_level = wf.declareStr("placement-level").searchable()
            placement_level.assign(placement_result)
            
            # Task 2: Select starting course based on level
            course_selection = wf.execute(
                "select-starting-course",
                placement_level,
                student_data
            ).withRetries(2)
            
            # Task 3: Generate initial assessment
            assessment = wf.execute(
                "generate-assessment",
                course_selection
            )
            
            # Event: Wait for student to complete first assessment
            assessment_result = wf.waitForEvent(
                "assessment-completed"
            ).timeout(60 * 60 * 24)  # 24-hour timeout
            .withCorrelationId(student_id)
            .registeredAs(dict)
            
            # Error handling for timeout
            wf.handleError(
                assessment_result,
                LHErrorType.TIMEOUT,
                handler=lambda h: (
                    h.execute("notify-teacher",
                        student_id,
                        "Student assessment timeout"
                    ),
                    h.fail("assessment-timeout",
                        "Student did not complete assessment within 24 hours"
                    )
                )
            )
            
            # Verify assessment results
            verification = wf.execute(
                "verify-assessment",
                assessment_result
            )
            
            # Update student record with enrollment
            wf.execute(
                "update-student-enrollment",
                student_id,
                course_selection,
                assessment_result
            )
            
            # Return completion status
            return wf.declareStr("status").assign("enrolled")
        
        return Workflow("student-onboarding", onboarding_wf)
```

### Example 2: Task Worker Implementation

```python
# littlehorse/tasks/task-workers.py

from littlehorse.worker import LHTaskMethod, WorkerContext
import asyncio
from typing import Any, Dict
import json

class StudentTaskWorkers:
    """
    Implements the actual task execution logic.
    These run independently and can scale horizontally.
    """
    
    @LHTaskMethod("evaluate-initial-level")
    async def evaluate_initial_level(
        self,
        student_data: Dict[str, Any],
        ctx: WorkerContext
    ) -> str:
        """
        Evaluates student's initial level based on diagnostic test.
        Returns: "beginner" | "intermediate" | "advanced"
        """
        try:
            # Call the actual assessment engine
            from iteachxr.assessment.engine import DiagnosticTest
            
            test = DiagnosticTest(student_data["learning_style"])
            score = await test.administer()
            
            # Normalize score to level
            if score < 40:
                level = "beginner"
            elif score < 70:
                level = "intermediate"
            else:
                level = "advanced"
            
            ctx.log(f"Student {student_data.get('id')} placed at {level}")
            return level
            
        except Exception as e:
            ctx.log(f"Error evaluating level: {str(e)}")
            raise
    
    @LHTaskMethod("select-starting-course")
    async def select_starting_course(
        self,
        placement_level: str,
        student_data: Dict[str, Any],
        ctx: WorkerContext
    ) -> str:
        """
        Selects the most appropriate starting course.
        Returns: course_id
        """
        try:
            from iteachxr.curriculum.recommender import CourseRecommender
            
            recommender = CourseRecommender()
            course_id = await recommender.recommend(
                level=placement_level,
                learning_style=student_data["learning_style"],
                interests=student_data.get("interests", [])
            )
            
            ctx.log(f"Course selected: {course_id}")
            return course_id
            
        except Exception as e:
            ctx.log(f"Error selecting course: {str(e)}")
            raise
    
    @LHTaskMethod("verify-assessment")
    async def verify_assessment(
        self,
        assessment_result: Dict[str, Any],
        ctx: WorkerContext
    ) -> Dict[str, Any]:
        """
        Verifies assessment integrity and detects anomalies.
        This is part of the self-testing mechanism.
        """
        try:
            # Validate response patterns
            if not self._validate_response_patterns(assessment_result):
                raise ValueError("Suspicious response patterns detected")
            
            # Check for completion
            if not assessment_result.get("completed", False):
                raise ValueError("Assessment not properly completed")
            
            # Verify score calculation
            expected_score = await self._recalculate_score(assessment_result)
            actual_score = assessment_result.get("score", 0)
            
            if abs(expected_score - actual_score) > 2:  # Allow 2% variance
                ctx.log(f"Score mismatch: expected {expected_score}, got {actual_score}")
                raise ValueError("Score calculation mismatch")
            
            return {
                "verified": True,
                "score": actual_score,
                "confidence": 0.95
            }
            
        except Exception as e:
            ctx.log(f"Assessment verification failed: {str(e)}")
            raise
    
    def _validate_response_patterns(self, result: Dict) -> bool:
        """Check for suspicious patterns indicating fraud or error."""
        # Check timing patterns
        if result.get("avg_response_time", 0) < 0.5:  # Too fast
            return False
        
        # Check consistency
        if result.get("responses_changed", 0) > len(result.get("responses", [])) * 0.3:
            return False  # More than 30% changed
        
        return True
    
    async def _recalculate_score(self, result: Dict) -> float:
        """Independently verify the score calculation."""
        # Implement score calculation logic
        pass
```

### Example 3: API Bridge Layer

```python
# api/littlehorse-bridge.py

from fastapi import FastAPI, HTTPException, BackgroundTasks
from littlehorse.config import LHConfig
from littlehorse.client import LittleHorseClient
from pydantic import BaseModel
from typing import Optional, Dict, Any
import json

app = FastAPI(title="iTeachXR LittleHorse API Bridge")

class WorkflowRunRequest(BaseModel):
    workflow_name: str
    variables: Dict[str, Any]
    correlation_id: Optional[str] = None

class EventTriggerRequest(BaseModel):
    event_name: str
    payload: Dict[str, Any]

@app.post("/api/students/enroll")
async def enroll_student(student_data: Dict[str, Any], bg_tasks: BackgroundTasks):
    """
    Auto-generated: Enrolls a student and starts onboarding workflow.
    Generated because this is a primary business process.
    """
    try:
        config = LHConfig()
        client = LittleHorseClient(config)
        
        # Start the student onboarding workflow
        wf_run = client.run_workflow(
            workflow_name="student-onboarding",
            variables={
                "student-id": student_data["id"],
                "student-data": student_data
            }
        )
        
        # Store run ID for tracking
        bg_tasks.add_task(track_workflow_run, wf_run.id)
        
        return {
            "workflow_run_id": wf_run.id,
            "status": "started",
            "student_id": student_data["id"]
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.post("/api/workflows/{workflow_name}/run/{run_id}/event/{event_name}")
async def trigger_workflow_event(
    workflow_name: str,
    run_id: str,
    event_name: str,
    request: EventTriggerRequest
):
    """
    Auto-generated: Triggers a workflow event (human intervention).
    Enables teachers to override agent decisions.
    """
    try:
        config = LHConfig()
        client = LittleHorseClient(config)
        
        # Send event to running workflow
        client.post_event(
            wf_run_id=run_id,
            event_name=event_name,
            event_payload=request.payload
        )
        
        return {
            "event_triggered": True,
            "workflow_run_id": run_id,
            "event_name": event_name
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@app.get("/api/workflows/health")
async def health_check():
    """
    Auto-generated: System health status.
    Anticipated from operational monitoring needs.
    """
    try:
        config = LHConfig()
        client = LittleHorseClient(config)
        
        metrics = client.get_system_metrics()
        
        return {
            "status": "healthy" if metrics["error_rate"] < 0.05 else "degraded",
            "queue_depth": metrics["queue_depth"],
            "error_rate": metrics["error_rate"],
            "avg_execution_time": metrics["avg_execution_time"]
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

async def track_workflow_run(run_id: str):
    """Background task to monitor workflow execution."""
    config = LHConfig()
    client = LittleHorseClient(config)
    
    # Poll until completion
    while True:
        status = client.get_wf_run(run_id)
        if status.status in ["COMPLETED", "FAILED"]:
            # Log results
            break
        await asyncio.sleep(5)
```

---

## Non-Breaking Integration Strategy

### 1. **Parallel System Approach**
```
Current System (Weeks 1-2):
- Existing curriculum engine runs as-is
- New LittleHorse workflows run alongside
- Both update same database through transactional adapters
- Teachers use existing interfaces (no change needed)

Gradual Migration (Weeks 3-6):
- Slowly route new students to LittleHorse flows
- Maintain fallback to legacy system
- Monitor metrics continuously
- Rollback capability at any point

Full Integration (Week 6+):
- All new students use LittleHorse workflows
- Legacy students continue on old system
- Gradual data migration of existing students
- Legacy system becomes read-only archive
```

### 2. **Database Integration Layer**

```python
# api/database-adapter.py

class DatabaseAdapter:
    """
    Ensures LittleHorse and existing system stay in sync.
    This is the non-breaking glue layer.
    """
    
    async def sync_student_progress(
        self,
        student_id: str,
        workflow_state: Dict,
        legacy_system_state: Dict
    ) -> bool:
        """
        Reconciles state between systems.
        Uses transaction to prevent data corruption.
        """
        async with database.transaction():
            try:
                # Check for conflicts
                if self._has_conflict(workflow_state, legacy_system_state):
                    # Apply conflict resolution policy
                    resolved = self._resolve_conflict(
                        workflow_state,
                        legacy_system_state
                    )
                else:
                    resolved = {**legacy_system_state, **workflow_state}
                
                # Update database atomically
                await db.update_student(student_id, resolved)
                
                return True
                
            except Exception as e:
                # Rollback on any error
                raise
    
    def _has_conflict(self, state1: Dict, state2: Dict) -> bool:
        """Detect concurrent modifications."""
        return (
            state1.get("last_modified") != state2.get("last_modified")
            and state1 != state2
        )
    
    def _resolve_conflict(self, state1: Dict, state2: Dict) -> Dict:
        """
        Merge conflict: prefer most recent, log discrepancy for review.
        """
        result = state2.copy()
        
        # Use timestamps to determine precedence
        for key, val1 in state1.items():
            if key in state2:
                ts1 = state1.get(f"{key}_ts", 0)
                ts2 = state2.get(f"{key}_ts", 0)
                result[key] = val1 if ts1 > ts2 else state2[key]
        
        return result
```

### 3. **Feature Flags for Safe Rollout**

```python
# config/feature-flags.py

FEATURE_FLAGS = {
    "use_littlehorse_onboarding": {
        "enabled": True,
        "rollout_percentage": 10,  # Start with 10% of new students
        "regions": ["US-WEST"],  # Start with one region
        "rollback_trigger": "error_rate > 0.05"
    },
    "use_littlehorse_progression": {
        "enabled": True,
        "rollout_percentage": 5,
        "regions": [],  # Not yet enabled
        "rollback_trigger": "any"
    },
    "use_littlehorse_assessment": {
        "enabled": False,
        "rollout_percentage": 0,
        "regions": [],
        "rollback_trigger": None
    }
}

async def should_use_littlehorse(feature: str, student_id: str) -> bool:
    """Determine whether to use LittleHorse for this student."""
    flags = FEATURE_FLAGS.get(feature)
    
    if not flags["enabled"]:
        return False
    
    # Hash student_id to consistently assign to control/treatment
    hash_val = hash(student_id) % 100
    if hash_val >= flags["rollout_percentage"]:
        return False
    
    # Check if in allowed region
    student_region = await get_student_region(student_id)
    if flags["regions"] and student_region not in flags["regions"]:
        return False
    
    return True
```

---

## Monitoring & Observability

### Metrics Dashboard

```
Real-time Metrics:
├── Workflow Metrics
│   ├── Active WfRuns: [count]
│   ├── Avg Execution Time: [ms]
│   ├── Success Rate: [%]
│   ├── Error Rate: [%]
│   └── Queue Depth: [count]
├── Task Metrics
│   ├── TaskRun Count: [count]
│   ├── Avg Task Duration: [ms]
│   ├── Task Retry Rate: [%]
│   └── Failed TaskRuns: [count]
├── Event Metrics
│   ├── Events Processed: [count]
│   ├── Event Processing Latency: [ms]
│   └── Missed Events: [count]
└── System Health
    ├── Kafka Lag: [ms]
    ├── Database Connections: [count]
    ├── Memory Usage: [%]
    └── CPU Usage: [%]

Alerts:
- Error rate > 5%: CRITICAL
- Queue depth > 10000: WARNING
- Execution time > 2x baseline: WARNING
- Failed events > 10: CRITICAL
```

---

## Security & Compliance

### Access Control
```
Authentication:
- OAuth2 for teacher/admin login
- mTLS for service-to-service communication
- API keys for external integrations

Authorization:
- Teachers: Can view own students, trigger interventions
- Admins: Full system access
- System: Service account with workflow execution permissions

Audit Logging:
- All workflow modifications
- All human interventions
- All error events
- All API calls (with rate limiting)
```

---

## Success Metrics

```
Technical KPIs:
✓ 99.9% uptime for workflow engine
✓ <100ms event processing latency
✓ Zero data consistency issues
✓ 0% breaking changes to existing APIs

Business KPIs:
✓ Agent-autonomous decisions for 80% of student progressions
✓ Teacher intervention required < 15% of the time
✓ System auto-corrects 95% of detected errors
✓ 50% faster student onboarding
✓ 30% improvement in student learning outcomes

Operational KPIs:
✓ 100% successful workflow execution (with retries)
✓ 99.5% test pass rate for auto-generated APIs
✓ Zero unintended side effects from auto-corrections
✓ <5min mean time to detect critical issues
```

---

## Support & Troubleshooting

### Common Issues & Resolutions

| Issue | Cause | Resolution |
|-------|-------|-----------|
| Workflow stuck in queue | Kafka lag or worker unavailable | Check broker health, restart workers |
| High latency | Database contention | Add replicas, implement caching |
| Frequent timeouts | Task complexity | Adjust timeout parameters, parallelize |
| Data inconsistency | Concurrent modifications | Use adapter's conflict resolution |
| Failed interventions | Event lost | Implement event replay from Kafka |

### Contact & Escalation
```
Tier 1 (Dashboard): Auto-healing via quality assurance workflow
Tier 2 (Automated): Alert sent to ops team, metrics reviewed
Tier 3 (Engineer): Human review triggered if auto-remediation fails
Tier 4 (Escalation): Technical leadership if data consistency at risk
```

---

## Next Steps

1. **Review & Approve** this integration strategy
2. **Setup Infrastructure**: Deploy LittleHorse server + Kafka cluster
3. **Implement Phase 1**: Start with student onboarding workflow
4. **Monitor & Iterate**: Gather feedback, refine workflows
5. **Expand Capabilities**: Progress through phases 2-4

---

## References

- [LittleHorse Documentation](https://littlehorse.io/docs/server)
- [Business-as-Code Patterns](https://littlehorse.io/docs/server/concepts)
- [Workflow Best Practices](https://littlehorse.io/docs/server/guides)
- [Event-Driven Architecture](https://littlehorse.io/docs/server/concepts/events)
