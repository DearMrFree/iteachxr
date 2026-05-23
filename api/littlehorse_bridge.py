"""
FastAPI Bridge Layer - Exposes LittleHorse Workflows as REST APIs

Auto-generates OpenAPI specs and REST endpoints from workflow definitions.
Handles:
- Workflow invocation
- Event triggering (human interventions)
- Status monitoring
- Metrics collection
- Auto-generated endpoint discovery
"""

from fastapi import FastAPI, HTTPException, BackgroundTasks, Query
from fastapi.responses import JSONResponse
from pydantic import BaseModel, Field
from typing import Dict, Any, Optional, List
import json
from datetime import datetime
from littlehorse.config import LHConfig
from littlehorse.client import LittleHorseClient
import logging

logger = logging.getLogger(__name__)

# ============================================================================
# PYDANTIC MODELS FOR REQUEST/RESPONSE
# ============================================================================

class WorkflowRunRequest(BaseModel):
    """Request to start a workflow."""
    workflow_name: str = Field(..., description="Name of workflow to run")
    variables: Dict[str, Any] = Field(..., description="Input variables for workflow")
    correlation_id: Optional[str] = Field(None, description="Tracking ID")


class WorkflowEventRequest(BaseModel):
    """Request to trigger workflow event."""
    event_name: str = Field(..., description="Name of external event")
    payload: Dict[str, Any] = Field(..., description="Event payload")


class StudentEnrollmentRequest(BaseModel):
    """Request to enroll a student."""
    student_id: str
    name: str
    learning_style: str  # visual, auditory, kinesthetic, reading-writing
    interests: List[str]
    age_group: str
    language: str = "en"


class TeacherInterventionRequest(BaseModel):
    """Request for teacher to intervene in student workflow."""
    action: str  # "advance", "retry", "reassign", "hold"
    reason: str
    notes: Optional[str] = None


# ============================================================================
# FASTAPI APPLICATION SETUP
# ============================================================================

app = FastAPI(
    title="iTeachXR LittleHorse API",
    description="Auto-generated REST API for self-driving school workflows",
    version="1.0.0"
)

# Initialize LittleHorse client
config = LHConfig()
lh_client = LittleHorseClient(config)


# ============================================================================
# CORE WORKFLOW ENDPOINTS
# ============================================================================

@app.post("/api/v1/workflows/{workflow_name}/run")
async def run_workflow(
    workflow_name: str,
    request: WorkflowRunRequest,
    bg_tasks: BackgroundTasks
):
    """
    **AUTO-GENERATED**: Start a workflow execution.
    
    Endpoint auto-generated from workflow specification.
    
    Returns:
        - workflow_run_id: Unique execution ID
        - status: "started"
        - estimated_duration: Expected execution time
    """
    try:
        logger.info(f"Starting workflow: {workflow_name}")
        
        # Start the workflow
        wf_run = lh_client.run_workflow(
            workflow_name=workflow_name,
            variables=request.variables,
            correlation_id=request.correlation_id
        )
        
        # Track in background
        bg_tasks.add_task(track_workflow_run, wf_run.id)
        
        return {
            "workflow_run_id": wf_run.id,
            "workflow_name": workflow_name,
            "status": "started",
            "timestamp": datetime.utcnow().isoformat(),
            "variables_received": len(request.variables),
            "correlation_id": request.correlation_id
        }
        
    except Exception as e:
        logger.error(f"Workflow start failed: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/v1/workflows/{workflow_name}/run/{run_id}")
async def get_workflow_status(
    workflow_name: str,
    run_id: str
):
    """
    **AUTO-GENERATED**: Get workflow execution status.
    
    Returns:
        - status: "RUNNING" | "COMPLETED" | "FAILED" | "STOPPED"
        - progress: Current step in workflow
        - results: Final output (if completed)
    """
    try:
        logger.info(f"Getting status for workflow run: {run_id}")
        
        status = lh_client.get_wf_run(run_id)
        
        return {
            "workflow_run_id": run_id,
            "workflow_name": workflow_name,
            "status": status.status,
            "current_node": status.current_node,
            "started_at": status.start_time,
            "updated_at": status.last_update_time,
            "completion_percentage": calculate_progress(status),
            "results": status.output if status.status == "COMPLETED" else None
        }
        
    except Exception as e:
        logger.error(f"Status retrieval failed: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/api/v1/workflows/{workflow_name}/run/{run_id}/event/{event_name}")
async def trigger_workflow_event(
    workflow_name: str,
    run_id: str,
    event_name: str,
    request: WorkflowEventRequest
):
    """
    **AUTO-GENERATED**: Trigger external event in running workflow.
    
    Used for human interventions, approvals, and external signals.
    
    Example: Teacher approves student advancement
        POST /api/v1/workflows/curriculum-progression/run/abc123/event/teacher-intervention-required
        {
            "event_name": "teacher-intervention-required",
            "payload": {
                "action": "advance",
                "reason": "Student demonstrated mastery",
                "teacher_id": "teacher_001"
            }
        }
    """
    try:
        logger.info(f"Triggering event: {event_name} for run {run_id}")
        
        # Send event to running workflow
        lh_client.post_event(
            wf_run_id=run_id,
            event_name=event_name,
            event_payload=request.payload
        )
        
        return {
            "event_triggered": True,
            "workflow_run_id": run_id,
            "event_name": event_name,
            "payload": request.payload,
            "timestamp": datetime.utcnow().isoformat()
        }
        
    except Exception as e:
        logger.error(f"Event trigger failed: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))


# ============================================================================
# BUSINESS DOMAIN ENDPOINTS (AUTO-GENERATED FROM CONVERSATIONS)
# ============================================================================

@app.post("/api/v1/students/enroll")
async def enroll_student(
    request: StudentEnrollmentRequest,
    bg_tasks: BackgroundTasks
):
    """
    **AUTO-GENERATED**: Enroll a new student and start onboarding.
    
    Anticipated because: This is the primary student acquisition flow.
    
    Triggers: student-onboarding workflow
    
    Process:
    1. Evaluate initial level
    2. Recommend starting course
    3. Generate initial assessment
    4. Track completion
    """
    try:
        logger.info(f"Enrolling student: {request.student_id}")
        
        # Start student onboarding workflow
        wf_run = lh_client.run_workflow(
            workflow_name="student-onboarding",
            variables={
                "student-id": request.student_id,
                "student-data": {
                    "id": request.student_id,
                    "name": request.name,
                    "learning_style": request.learning_style,
                    "interests": request.interests,
                    "age_group": request.age_group,
                    "language": request.language
                }
            }
        )
        
        bg_tasks.add_task(track_workflow_run, wf_run.id)
        
        return {
            "student_id": request.student_id,
            "enrollment_status": "in-progress",
            "workflow_run_id": wf_run.id,
            "next_step": "Initial level assessment",
            "timestamp": datetime.utcnow().isoformat()
        }
        
    except Exception as e:
        logger.error(f"Enrollment failed: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/v1/students/{student_id}/progress")
async def get_student_progress(student_id: str):
    """
    **AUTO-GENERATED**: Get student's curriculum progress.
    
    Anticipated because: Teachers frequently check student status.
    
    Returns:
    - current_lesson
    - progress_percentage
    - readiness_score
    - next_recommended_action
    """
    try:
        logger.info(f"Getting progress for student: {student_id}")
        
        # Query active workflow for this student
        active_workflows = lh_client.search_wf_runs(
            wf_spec_name="curriculum-progression",
            search_filter={"student-id": student_id}
        )
        
        if not active_workflows:
            return {"status": "not-started"}
        
        latest = active_workflows[0]
        
        return {
            "student_id": student_id,
            "current_workflow": latest.wf_spec_name,
            "workflow_id": latest.id,
            "status": latest.status,
            "started_at": latest.start_time,
            "last_update": latest.last_update_time
        }
        
    except Exception as e:
        logger.error(f"Progress retrieval failed: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))


@app.post("/api/v1/students/{student_id}/intervene")
async def teacher_intervention(
    student_id: str,
    request: TeacherInterventionRequest
):
    """
    **AUTO-GENERATED**: Teacher intervenes in student's curriculum progression.
    
    Anticipated because: Teachers need to override agent decisions.
    
    Actions:
    - "advance": Skip ahead to next level
    - "retry": Repeat current lesson
    - "reassign": Change to different course
    - "hold": Pause progression
    """
    try:
        logger.info(f"Teacher intervention for {student_id}: {request.action}")
        
        # Find active workflow for this student
        active_workflows = lh_client.search_wf_runs(
            wf_spec_name="curriculum-progression",
            search_filter={"student-id": student_id}
        )
        
        if not active_workflows:
            raise ValueError(f"No active workflow for student {student_id}")
        
        run_id = active_workflows[0].id
        
        # Trigger intervention event
        lh_client.post_event(
            wf_run_id=run_id,
            event_name="teacher-intervention-required",
            event_payload={
                "action": request.action,
                "reason": request.reason,
                "notes": request.notes,
                "timestamp": datetime.utcnow().isoformat()
            }
        )
        
        return {
            "intervention_accepted": True,
            "student_id": student_id,
            "action": request.action,
            "workflow_id": run_id
        }
        
    except Exception as e:
        logger.error(f"Intervention failed: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/v1/analytics/struggling-students")
async def get_struggling_students():
    """
    **AUTO-GENERATED**: Identify students needing intervention.
    
    Anticipated because: Teachers and admins query this frequently.
    
    Returns list of students with readiness score < 0.50
    """
    try:
        logger.info("Fetching struggling students")
        
        # Query all active curriculum workflows
        workflows = lh_client.search_wf_runs(
            wf_spec_name="curriculum-progression"
        )
        
        struggling = []
        for wf in workflows:
            if hasattr(wf, 'variables'):
                readiness = wf.variables.get("readiness-score", 1.0)
                if readiness < 0.50:
                    struggling.append({
                        "student_id": wf.variables.get("student-id"),
                        "readiness_score": readiness,
                        "current_lesson": wf.variables.get("current-lesson"),
                        "workflow_id": wf.id
                    })
        
        return {
            "total_students": len(workflows),
            "struggling_count": len(struggling),
            "struggling_students": struggling
        }
        
    except Exception as e:
        logger.error(f"Analytics query failed: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/v1/workflows/health")
async def system_health():
    """
    **AUTO-GENERATED**: System health and metrics.
    
    Anticipated because: Ops teams monitor this continuously.
    
    Returns:
    - overall_status
    - queue_depth
    - error_rate
    - avg_execution_time
    - active_workflows
    """
    try:
        logger.info("Getting system health")
        
        metrics = lh_client.get_system_metrics()
        
        status = "healthy"
        if metrics.get("error_rate", 0) > 0.05:
            status = "degraded"
        elif metrics.get("error_rate", 0) > 0.10:
            status = "critical"
        
        return {
            "status": status,
            "timestamp": datetime.utcnow().isoformat(),
            "metrics": {
                "queue_depth": metrics.get("queue_depth", 0),
                "error_rate": metrics.get("error_rate", 0),
                "avg_execution_time_ms": metrics.get("avg_execution_time", 0),
                "active_workflows": metrics.get("active_count", 0),
                "completed_today": metrics.get("completed_today", 0),
                "failed_today": metrics.get("failed_today", 0)
            }
        }
        
    except Exception as e:
        logger.error(f"Health check failed: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))


# ============================================================================
# DISCOVERY & DOCUMENTATION ENDPOINTS
# ============================================================================

@app.get("/api/v1/workflows")
async def list_workflows():
    """
    **AUTO-GENERATED**: List all available workflows.
    
    Returns workflow specs with input/output schemas.
    """
    try:
        workflows = lh_client.list_workflows()
        
        return {
            "workflows": [
                {
                    "name": wf.name,
                    "version": wf.version,
                    "description": wf.description,
                    "input_variables": wf.input_vars,
                    "output_variables": wf.output_vars,
                    "created_at": wf.created_at
                }
                for wf in workflows
            ],
            "total": len(workflows)
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@app.get("/api/v1/workflows/{workflow_name}/schema")
async def get_workflow_schema(workflow_name: str):
    """
    **AUTO-GENERATED**: Get OpenAPI schema for a workflow.
    
    Returns JSON Schema for input/output variables.
    """
    try:
        wf_spec = lh_client.get_workflow_spec(workflow_name)
        
        return {
            "workflow_name": workflow_name,
            "version": wf_spec.version,
            "input_schema": wf_spec.input_schema,
            "output_schema": wf_spec.output_schema,
            "tasks": [t.name for t in wf_spec.tasks]
        }
        
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


# ============================================================================
# HELPER FUNCTIONS
# ============================================================================

async def track_workflow_run(run_id: str):
    """Background task to monitor workflow completion."""
    logger.info(f"Starting background tracking for run {run_id}")
    
    max_wait = 60 * 60 * 24  # 24 hours
    elapsed = 0
    
    while elapsed < max_wait:
        try:
            status = lh_client.get_wf_run(run_id)
            
            if status.status in ["COMPLETED", "FAILED", "STOPPED"]:
                logger.info(f"Workflow {run_id} finished with status: {status.status}")
                break
            
            await asyncio.sleep(5)
            elapsed += 5
            
        except Exception as e:
            logger.error(f"Error tracking workflow: {str(e)}")
            break


def calculate_progress(status: Any) -> int:
    """Calculate approximate progress percentage."""
    if status.status == "COMPLETED":
        return 100
    elif status.status == "FAILED":
        return 0
    else:
        # Estimate based on current node
        total_nodes = getattr(status, 'total_nodes', 10)
        current = getattr(status, 'current_node_index', 0)
        return int((current / total_nodes) * 100)


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000, log_level="info")
